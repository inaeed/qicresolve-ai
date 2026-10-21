<?php

namespace App\Http\Controllers;

use App\Models\{QicIssue, QicEvidence, User};
use App\Services\QicEvidenceStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class QicIssueController extends Controller
{
    private function workspaceRole(Request $request): string
    {
        $role = (string) ($request->route('qicRole') ?? $request->input('workspace'));
        abort_unless(in_array($role, ['main-assy','auto-line','quality'], true)
            && $request->user()->canAccessWorkspace(str_replace('-', '_', $role)), 403);
        return $role;
    }
    private function findIssue(Request $request, string $code): QicIssue
    {
        return QicIssue::visibleTo($request->user())->where('code', $code)->firstOrFail();
    }
    public function page(Request $request)
    {
        $role = $this->workspaceRole($request);
        $page = $request->route('qicPage');
        $definition = config('qic_ui')[$role]['pages'][$page];
        $issue = $request->route('issue') ? $this->findIssue($request, $request->route('issue')) : null;
        if ($issue) $issue->load(['evidence','responses.evidence','responses.author','audits.actor','reporter','owner']);
        $query = QicIssue::visibleTo($request->user())->with(['owner','reporter'])->latest('updated_at')->latest('id');
        if ($request->filled('search')) {
            $search = '%'.$request->string('search').'%';
            $query->where(fn ($q) => $q->where('code','like',$search)->orWhere('material','like',$search)->orWhere('description','like',$search));
        }
        if (array_key_exists((string)$request->input('status'), config('qic_status'))) $query->where('status', $request->input('status'));
        if ($page === 'reviews.index') $query->where('status','waiting_review');
        if ($page === 'assignments') $query->where('status','verified');
        $all = $query->get();
        $form = $page === 'issues.create' || $page === 'issues.edit';
        if ($form) {
            abort_unless($role === 'main-assy', 403);
            if ($issue) abort_unless(in_array($issue->status, ['draft','need_information'], true), 409);
        }
        return view('qic-server.workspace', [
            'qicRole'=>$role,'qicPage'=>$page,'qicScreen'=>$definition['screen'],
            'qicTitle'=>$definition['title'], 'serverBacked'=>true,
            'issue'=>$issue,'records'=>$all,'showForm'=>$form,
            'owners'=>User::where('role','auto_line')->orderBy('name')->get(),
            'statuses'=>config('qic_status'),
        ]);
    }
    private function audit(QicIssue $issue, int $user, string $action, ?string $previous, ?string $note = null): void
    {
        $issue->audits()->create(['actor_id'=>$user,'action'=>$action,'from_status'=>$previous,'to_status'=>$issue->status,'note'=>$note]);
    }
    public function saveComplaint(Request $request, QicEvidenceStore $files)
    {
        $role = $this->workspaceRole($request);
        abort_unless($role === 'main-assy', 403);
        $data = $request->validate([
            'code'=>['nullable','string','max:40'], 'material'=>['required','string','max:200'],
            'material_name'=>['nullable','string','max:200'], 'batch'=>['nullable','string','max:100'],
            'defect'=>['required','string','max:200'], 'quantity'=>['required','integer','min:1','max:1000000'],
            'priority'=>['required', Rule::in(['Normal','Tinggi'])],
            'description'=>['required','string','max:5000'], 'occurred_at'=>['required','date','before_or_equal:now'],
            'defect_photo'=>['nullable','file','mimes:jpg,jpeg,png,webp','max:5120'],
            'label_photo'=>['nullable','file','mimes:jpg,jpeg,png,webp,pdf','max:5120'],
            'submit'=>['required',Rule::in(['draft','submit'])],
        ]);
        try {
            $issue = DB::transaction(function () use ($request, $data, $files) {
                $issue = !empty($data['code'])
                    ? QicIssue::visibleTo($request->user())->where('code',$data['code'])->lockForUpdate()->firstOrFail()
                    : new QicIssue(['reporter_id'=>$request->user()->id]);
                if ($issue->exists) abort_unless(in_array($issue->status,['draft','need_information'],true),409);
                $previous = $issue->exists ? $issue->status : null;
                if ($data['submit'] === 'submit') {
                    foreach (['defect_photo'=>'defect','label_photo'=>'label'] as $field=>$category) {
                        if (!$request->hasFile($field) && (!$issue->exists || !$issue->evidence()->where('category',$category)->exists())) {
                            throw ValidationException::withMessages([$field=>'Lengkapi foto defect dan label material.']);
                        }
                    }
                }
                $issue->fill(collect($data)->only(['material','material_name','batch','defect','quantity','priority','description','occurred_at'])->all());
                $issue->status = $data['submit'] === 'submit' ? 'waiting_verification' : ($previous === 'need_information' ? 'need_information' : 'draft');
                $issue->save();
                if (!$issue->code) { $issue->code='QI-'.str_pad((string)$issue->id,3,'0',STR_PAD_LEFT); $issue->save(); }
                foreach (['defect_photo'=>'defect','label_photo'=>'label'] as $field=>$category) {
                    if ($upload=$request->file($field)) $files->put($issue,$upload->getRealPath(),$upload->getClientOriginalName(),$upload->getMimeType(),$category,$request->user()->id);
                }
                $this->audit($issue,$request->user()->id,'Complaint disimpan',$previous);
                return $issue;
            });
        } catch (Throwable $e) { $files->rollbackFiles(); throw $e; }
        return redirect()->route($role.'.issues.show',['issue'=>$issue->code])->with('qic_status','Complaint dan evidence tersimpan di server.');
    }
    public function action(Request $request, string $code, QicEvidenceStore $files)
    {
        $role=$this->workspaceRole($request);
        $data=$request->validate([
            'action'=>['required',Rule::in(['verify','return_info','reject','assign','acknowledge','investigate','submit_3c','approve','revise','reject_3c','corrective','close','return_corrective'])],
            'note'=>['nullable','string','max:5000'], 'owner_id'=>['nullable','integer',Rule::exists('users','id')->where('role','auto_line')],
            'investigation'=>['nullable','string','max:5000'],'containment'=>['nullable','string','max:5000'],
            'concern'=>['nullable','string','max:5000'],'cause'=>['nullable','string','max:5000'],'countermeasure'=>['nullable','string','max:5000'],
            'corrective_action'=>['nullable','string','max:5000'],'corrective_result'=>['nullable','string','max:5000'],
            'evidence'=>['nullable','array','max:10'], 'evidence.*'=>['file','mimes:jpg,jpeg,png,webp,pdf,xlsx','max:5120'],
        ]);
        $rules=[
            'verify'=>['quality',['waiting_verification'],'verified'],
            'return_info'=>['quality',['waiting_verification'],'need_information'],
            'reject'=>['quality',['waiting_verification'],'rejected'],
            'assign'=>['quality',['verified'],'assigned'],
            'acknowledge'=>['auto-line',['assigned'],'investigation'],
            'investigate'=>['auto-line',['investigation','revision_requested','three_c_rejected'],'investigation'],
            'submit_3c'=>['auto-line',['investigation','revision_requested','three_c_rejected'],'waiting_review'],
            'approve'=>['quality',['waiting_review'],'approved'],
            'revise'=>['quality',['waiting_review'],'revision_requested'],
            'reject_3c'=>['quality',['waiting_review'],'three_c_rejected'],
            'corrective'=>['auto-line',['approved','corrective'],'waiting_final'],
            'close'=>['quality',['waiting_final'],'closed'],
            'return_corrective'=>['quality',['waiting_final'],'corrective'],
        ];
        try {
            $issue=DB::transaction(function () use ($request,$data,$rules,$role,$files,$code) {
                $issue=QicIssue::visibleTo($request->user())->where('code',$code)->lockForUpdate()->firstOrFail();
                [$requiredRole,$allowed,$next]=$rules[$data['action']];
                abort_unless($role===$requiredRole,403);
                if (!in_array($issue->status,$allowed,true)) throw ValidationException::withMessages(['action'=>'Status sudah berubah atau tindakan tidak sesuai tahap. Muat ulang halaman.']);
                $previous=$issue->status;
                $require=function (array $fields) use ($data) { foreach ($fields as $field) if (empty(trim((string)($data[$field]??'')))) throw ValidationException::withMessages([$field=>'Kolom ini wajib diisi.']); };
                if (in_array($data['action'],['return_info','reject','approve','revise','reject_3c','close','return_corrective'],true)) $require(['note']);
                if ($data['action']==='assign') { $require(['owner_id']); $issue->owner_id=$data['owner_id']; }
                if ($data['action']==='investigate') { $require(['investigation','containment']); $issue->fill(collect($data)->only(['investigation','containment'])->all()); }
                $response=null;
                if ($data['action']==='submit_3c') {
                    $require(['concern','cause','countermeasure']);
                    if (empty($issue->investigation)||empty($issue->containment)) throw ValidationException::withMessages(['action'=>'Simpan investigasi dan containment terlebih dahulu.']);
                    $response=$issue->responses()->create([
                        'author_id'=>$request->user()->id,'version'=>($issue->responses()->max('version')??0)+1,
                        'concern'=>$data['concern'],'cause'=>$data['cause'],'countermeasure'=>$data['countermeasure'],
                    ]);
                }
                if (in_array($data['action'],['approve','revise','reject_3c'],true)) {
                    $response=$issue->responses()->firstOrFail();
                    $response->update(['decision'=>$data['action'],'review_note'=>$data['note'],'reviewer_id'=>$request->user()->id,'reviewed_at'=>now()]);
                    $response=null; // Quality evidence must not overwrite the submitted version's attachments.
                }
                if ($data['action']==='corrective') {
                    $require(['corrective_action','corrective_result']);
                    if (!$request->hasFile('evidence')) throw ValidationException::withMessages(['evidence'=>'Unggah bukti hasil tindakan.']);
                    $issue->fill(collect($data)->only(['corrective_action','corrective_result'])->all());
                }
                if ($data['action']==='close' && (!$issue->corrective_result || !$issue->evidence()->where('category','corrective')->exists())) {
                    throw ValidationException::withMessages(['action'=>'Hasil dan bukti tindakan korektif harus tersedia.']);
                }
                $category=match($data['action']) {'submit_3c'=>'response','corrective'=>'corrective',default=>'investigation'};
                foreach ($request->file('evidence',[]) as $upload) $files->put($issue,$upload->getRealPath(),$upload->getClientOriginalName(),$upload->getMimeType(),$category,$request->user()->id,$response?->id);
                $issue->status=$next; $issue->touch(); $issue->save();
                $this->audit($issue,$request->user()->id,$data['action'],$previous,$data['note']??null);
                return $issue;
            });
        } catch (Throwable $e) { $files->rollbackFiles(); throw $e; }
        return redirect()->route($role.'.issues.show',['issue'=>$issue->code])->with('qic_status','Tindakan dan evidence tersimpan di database.');
    }
    public function evidence(Request $request, QicEvidence $evidence)
    {
        $this->findIssue($request,$evidence->issue->code);
        abort_unless(Storage::disk('local')->exists($evidence->path),404,'Berkas evidence tidak ditemukan di storage.');
        $inline=in_array($evidence->mime,['image/jpeg','image/png','image/webp','application/pdf'],true) && !$request->boolean('download');
        return Storage::disk('local')->response($evidence->path,$evidence->original_name,
            ['Content-Type'=>$evidence->mime,'X-Content-Type-Options'=>'nosniff','Cache-Control'=>'private, no-store'], $inline?'inline':'attachment');
    }
}
