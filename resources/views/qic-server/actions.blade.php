@php
 $actionForms=[];
 if($qicRole==='quality') {
  if($issue->status==='waiting_verification')$actionForms=['verify'=>'Verifikasi','return_info'=>'Minta Kelengkapan','reject'=>'Tolak Issue'];
  if($issue->status==='verified')$actionForms=['assign'=>'Tugaskan Auto Line'];
  if($issue->status==='waiting_review')$actionForms=['approve'=>'Setujui 3C','revise'=>'Minta Revisi 3C','reject_3c'=>'Tolak 3C'];
  if($issue->status==='waiting_final')$actionForms=['close'=>'Verifikasi dan Tutup Issue','return_corrective'=>'Kembalikan Tindakan'];
 }
 if($qicRole==='auto-line') {
  if($issue->status==='assigned')$actionForms=['acknowledge'=>'Terima Penugasan'];
  if(in_array($issue->status,['investigation','revision_requested','three_c_rejected']))$actionForms=['investigate'=>'Simpan Investigasi dan Containment','submit_3c'=>'Kirim Versi 3C Baru'];
  if(in_array($issue->status,['approved','corrective']))$actionForms=['corrective'=>'Kirim Hasil Tindakan'];
 }
 if(str_starts_with($qicPage,'responses.')) $actionForms=array_intersect_key($actionForms,array_flip(['submit_3c']));
 if($qicPage==='corrective') $actionForms=array_intersect_key($actionForms,array_flip(['corrective']));
 if($qicPage==='reviews.show') $actionForms=array_intersect_key($actionForms,array_flip(['approve','revise','reject_3c']));
 if($qicPage==='issues.verify') $actionForms=array_intersect_key($actionForms,array_flip(['verify','return_info','reject']));
 if($qicPage==='final-verification') $actionForms=array_intersect_key($actionForms,array_flip(['close','return_corrective']));
@endphp
@foreach($actionForms as $action=>$title)
<section class="qic-card"><h2>{{ $title }}</h2><form method="POST" action="{{ route('qic.issues.action',['code'=>$issue->code]) }}" enctype="multipart/form-data">
@csrf<input type="hidden" name="workspace" value="{{ $qicRole }}"><input type="hidden" name="action" value="{{ $action }}">
@if($action==='assign')<label class="qic-field">Pilih penanggung jawab<select name="owner_id" required><option value="">Pilih Auto Line</option>@foreach($owners as $owner)<option value="{{ $owner->id }}">{{ $owner->name }}</option>@endforeach</select></label>@endif
@if($action==='investigate')
@foreach(['investigation'=>'Hasil investigasi','containment'=>'Containment / Pengendalian sementara'] as $field=>$label)<label class="qic-field">{{ $label }}<textarea name="{{ $field }}" required maxlength="5000">{{ old($field,$issue->$field) }}</textarea></label>@endforeach
@endif
@if($action==='submit_3c')
<p class="qic-small qic-muted">Setiap pengiriman membuat versi baru. Versi dan evidence sebelumnya tetap tersimpan.</p>
@foreach(['concern'=>'Concern','cause'=>'Cause (hipotesis/simulasi harus disebutkan)','countermeasure'=>'Countermeasure'] as $field=>$label)<label class="qic-field">{{ $label }}<textarea name="{{ $field }}" required maxlength="5000">{{ old($field) }}</textarea></label>@endforeach
@endif
@if($action==='corrective')
@foreach(['corrective_action'=>'Tindakan yang dilakukan','corrective_result'=>'Hasil pemeriksaan / validasi'] as $field=>$label)<label class="qic-field">{{ $label }}<textarea name="{{ $field }}" required maxlength="5000">{{ old($field,$issue->$field) }}</textarea></label>@endforeach
@endif
@if(in_array($action,['investigate','submit_3c','corrective']))<label class="qic-field">Evidence (maks. 10 berkas, masing-masing 5 MiB)<input type="file" name="evidence[]" multiple accept="image/jpeg,image/png,image/webp,application/pdf,.xlsx" @required($action==='corrective')></label>@endif
@if(in_array($action,['verify','return_info','reject','approve','revise','reject_3c','close','return_corrective']))<label class="qic-field">Catatan / alasan<textarea name="note" maxlength="5000" @required($action!=='verify')>{{ old('note') }}</textarea></label>@endif
<button class="qic-button" type="submit">{{ $title }}</button>
</form></section>
@endforeach
