// XLSX minimal tanpa dependency: satu worksheet, inline strings (bukan formula), ZIP uncompressed.
const encoder = new TextEncoder();
const xml = value => String(value ?? '').replace(/[\u0000-\u0008\u000B\u000C\u000E-\u001F]/g, '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&apos;');
function crc32(bytes) { let crc = 0xffffffff; for (const b of bytes) { crc ^= b; for (let i = 0; i < 8; i++) crc = (crc >>> 1) ^ ((crc & 1) ? 0xedb88320 : 0); } return (crc ^ 0xffffffff) >>> 0; }
function header(length, fields) { const b = new Uint8Array(length); const v = new DataView(b.buffer); for (const [offset, value, size = 2] of fields) size === 4 ? v.setUint32(offset, value, true) : v.setUint16(offset, value, true); return b; }
function zip(files) {
    const local = [], central = []; let offset = 0;
    for (const [name, text] of Object.entries(files)) {
        const n = encoder.encode(name), data = encoder.encode(text), crc = crc32(data);
        const h = header(30, [[0,0x04034b50,4],[4,20],[8,0],[12,33],[14,crc,4],[18,data.length,4],[22,data.length,4],[26,n.length]]);
        local.push(h,n,data);
        central.push(header(46,[[0,0x02014b50,4],[4,20],[6,20],[14,33],[16,crc,4],[20,data.length,4],[24,data.length,4],[28,n.length],[42,offset,4]]),n);
        offset += h.length + n.length + data.length;
    }
    const size = central.reduce((sum,b)=>sum+b.length,0), count = Object.keys(files).length;
    return new Blob([...local,...central,header(22,[[0,0x06054b50,4],[8,count],[10,count],[12,size,4],[16,offset,4]])],{type:'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
}
function column(index) { let n=index+1,s=''; while(n){n--;s=String.fromCharCode(65+n%26)+s;n=Math.floor(n/26);}return s; }
export function workbook(rows) {
    const sheet = rows.map((row,r)=>`<row r="${r+1}">${row.map((v,c)=>`<c r="${column(c)}${r+1}" t="inlineStr"><is><t xml:space="preserve">${xml(v)}</t></is></c>`).join('')}</row>`).join('');
    return zip({
        '[Content_Types].xml':'<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>',
        '_rels/.rels':'<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
        'xl/workbook.xml':'<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="QICResolve Demo" sheetId="1" r:id="rId1"/></sheets></workbook>',
        'xl/_rels/workbook.xml.rels':'<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>',
        'xl/worksheets/sheet1.xml':`<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>${sheet}</sheetData></worksheet>`,
    });
}
export function downloadWorkbook(rows, name='QICResolve_Demo.xlsx') {
    const url=URL.createObjectURL(workbook(rows)),a=document.createElement('a');a.href=url;a.download=name;document.body.append(a);a.click();a.remove();setTimeout(()=>URL.revokeObjectURL(url),1000);
}
