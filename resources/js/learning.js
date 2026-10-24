function setupQuestion(form) {
    const config=JSON.parse(form.dataset.question); const status=form.querySelector('[data-mission-status]');
    let dragging=null;
    if(config.type==='drag_drop'){
        const pool=form.querySelector('[data-word-pool]');let selected=null,gesture=null,ghost=null,ignoreClick=false;
        const select=item=>{selected=item;form.querySelectorAll('[data-item]').forEach(card=>{card.classList.toggle('is-selected',card===item);card.setAttribute('aria-pressed',String(card===item));});if(item)status.textContent='Pilih kategori untuk kad ini.';};
        const place=(item,key,notify=true)=>{const destination=key?form.querySelector(`[data-target="${key}"] [data-target-items]`):pool;if(!destination)return;destination.append(item);const input=item.querySelector('[data-category-value]');input.value=key;item.classList.remove('answer-rejected');if(notify){input.dispatchEvent(new Event('input',{bubbles:true}));status.textContent=key?'✓ Kad diletakkan.':'Kad dikembalikan.';}select(null);};
        form.querySelectorAll('[data-item]').forEach(item=>{
            item.addEventListener('click',event=>{event.stopPropagation();if(!ignoreClick)select(selected===item?null:item);});
            item.addEventListener('keydown',event=>{if(event.key==='Enter'||event.key===' '){event.preventDefault();event.stopPropagation();select(item);}if(event.key==='Escape')select(null);});
            item.querySelector('[data-category-value]').addEventListener('change',event=>place(item,event.target.value,false));
            item.addEventListener('pointerdown',event=>{if(event.button!==0)return;gesture={item,x:event.clientX,y:event.clientY,id:event.pointerId};});
        });
        form.addEventListener('pointermove',event=>{
            if(!gesture||event.pointerId!==gesture.id)return;
            if(!ghost&&Math.hypot(event.clientX-gesture.x,event.clientY-gesture.y)<8)return;
            event.preventDefault();
            if(!ghost){ghost=gesture.item.cloneNode(true);ghost.className='drag-card-ghost';ghost.setAttribute('aria-hidden','true');document.body.append(ghost);gesture.item.classList.add('is-dragging');select(gesture.item);gesture.item.setPointerCapture(event.pointerId);}
            ghost.style.left=`${event.clientX}px`;ghost.style.top=`${event.clientY}px`;
            const hit=document.elementFromPoint(event.clientX,event.clientY)?.closest('[data-target]');form.querySelectorAll('[data-target]').forEach(zone=>zone.classList.toggle('drag-over',zone===hit));
        },{passive:false});
        const release=event=>{
            if(!gesture)return;if(ghost){const hit=document.elementFromPoint(event.clientX,event.clientY)?.closest('[data-target],[data-word-pool]');if(hit)place(gesture.item,hit.dataset.target||'');ignoreClick=true;setTimeout(()=>ignoreClick=false,0);}
            gesture.item.classList.remove('is-dragging');ghost?.remove();ghost=null;gesture=null;form.querySelectorAll('[data-target]').forEach(zone=>zone.classList.remove('drag-over'));
        };
        form.addEventListener('pointerup',release);form.addEventListener('pointercancel',event=>{ghost?.remove();ghost=null;gesture?.item.classList.remove('is-dragging');gesture=null;});
        for(const zone of [...form.querySelectorAll('[data-target]'),pool]){
            zone.addEventListener('click',()=>{if(selected)place(selected,zone.dataset.target||'');});
            zone.addEventListener('keydown',event=>{if(event.target===zone&&(event.key==='Enter'||event.key===' ')){event.preventDefault();if(selected)place(selected,zone.dataset.target||'');}});
        }
        form.addEventListener('submit',event=>{if([...form.querySelectorAll('[data-category-value]')].some(input=>!input.value)){event.preventDefault();status.textContent='Letakkan semua kad dalam kategori dahulu.';}});
    }
    if(config.type==='sentence_order'){
        const pool=form.querySelector('[data-order-pool]'),answer=form.querySelector('[data-order-answer]'),inputs=form.querySelector('[data-order-inputs]');
        const sync=()=>{inputs.replaceChildren();answer.querySelectorAll('[data-token]').forEach(token=>{const input=document.createElement('input');input.type='hidden';input.name='answer[]';input.value=token.dataset.token;inputs.append(input);});status.textContent=answer.textContent.trim()?'Ayat anda: '+[...answer.querySelectorAll('[data-token]')].map(t=>t.textContent).join(' '):'Pilih blok untuk membina ayat.';};
        form.querySelectorAll('[data-token]').forEach(token=>{token.addEventListener('click',()=>{(token.parentElement===pool?answer:pool).append(token);sync();});token.addEventListener('dragstart',e=>{dragging=token;e.dataTransfer.setData('text/plain',token.dataset.token);});token.addEventListener('dragover',e=>e.preventDefault());token.addEventListener('drop',e=>{e.preventDefault();e.stopPropagation();if(dragging&&dragging!==token){token.parentElement.insertBefore(dragging,token);sync();}dragging=null;});});
        [pool,answer].forEach(zone=>{zone.addEventListener('dragover',e=>e.preventDefault());zone.addEventListener('drop',e=>{e.preventDefault();if(dragging){zone.append(dragging);sync();}dragging=null;});});
        form.querySelector('[data-order-reset]').addEventListener('click',()=>{[...answer.children].forEach(t=>pool.append(t));sync();});
        form.addEventListener('submit',e=>{if(answer.querySelectorAll('[data-token]').length!==Object.keys(config.config.items).length){e.preventDefault();status.textContent='Gunakan semua blok sekali sebelum menghantar jawapan.';}});
    }
    form.addEventListener('keydown',()=>{form.querySelector('[data-input-mode]').value='keyboard';});
    form.addEventListener('submit',()=>{if(form.checkValidity())form.querySelector('.mission-submit').textContent='Menyimpan jawapan…';});
}

function setupRecorder(el){
    const start=el.querySelector('[data-record]'),stop=el.querySelector('[data-stop]'),audio=el.querySelector('audio'),status=el.querySelector('[data-recording-status]');let stream,recorder,chunks=[],timer,url;
    const cleanup=()=>{clearTimeout(timer);stream?.getTracks().forEach(t=>t.stop());};
    start.addEventListener('click',async()=>{try{if(!window.MediaRecorder)throw Error();stream=await navigator.mediaDevices.getUserMedia({audio:true});chunks=[];const mime=['audio/webm','audio/mp4','audio/ogg'].find(t=>MediaRecorder.isTypeSupported(t));recorder=new MediaRecorder(stream,mime?{mimeType:mime}:undefined);recorder.ondataavailable=e=>{if(e.data.size)chunks.push(e.data);};recorder.onstop=()=>{cleanup();const blob=new Blob(chunks,{type:recorder.mimeType});const ext=recorder.mimeType.includes('mp4')?'m4a':(recorder.mimeType.includes('ogg')?'ogg':'webm');const file=new File([blob],`rakaman.${ext}`,{type:blob.type});const transfer=new DataTransfer();transfer.items.add(file);document.getElementById('studio-file').files=transfer.files;if(url)URL.revokeObjectURL(url);url=URL.createObjectURL(blob);audio.src=url;audio.hidden=false;start.disabled=false;stop.disabled=true;status.textContent='Rakaman sedia. Dengar pratonton, kemudian tekan Simpan untuk memuat naik.';};recorder.start();start.disabled=true;stop.disabled=false;status.textContent='Sedang merakam. Had 120 saat.';timer=setTimeout(()=>recorder.state==='recording'&&recorder.stop(),120000);}catch{cleanup();status.textContent='Mikrofon tidak tersedia atau izin ditolak. Anda boleh memilih fail audio.';}});
    stop.addEventListener('click',()=>recorder?.state==='recording'&&recorder.stop());window.addEventListener('pagehide',cleanup);document.addEventListener('visibilitychange',()=>{if(document.hidden&&recorder?.state==='recording')recorder.stop();});
}

document.querySelectorAll('[data-mission-question]').forEach(setupQuestion);
document.querySelectorAll('[data-recorder]').forEach(setupRecorder);

window.addEventListener('pagehide',()=>document.querySelectorAll('audio,video').forEach(el=>el.pause()));
// Keep browser-generated form prompts and media controls in Malay.
function localiseControls(root = document) {
    root.querySelectorAll('input,select,textarea').forEach(input => {
        if (input.dataset.malayValidation) return;
        input.dataset.malayValidation = 'true';
        const clear = () => input.setCustomValidity('');
        input.addEventListener('input', clear);
        input.addEventListener('change', clear);
        input.addEventListener('invalid', () => {
            clear();
            const v = input.validity;
            let message = 'Semak maklumat ini.';
            if (v.valueMissing) message = input.type === 'file' ? 'Pilih fail dahulu.' : (['radio','checkbox'].includes(input.type) || input.tagName === 'SELECT' ? 'Pilih satu jawapan.' : 'Sila isi ruangan ini.');
            else if (v.typeMismatch) message = input.type === 'email' ? 'Masukkan alamat e-mel yang sah.' : 'Masukkan maklumat yang sah.';
            else if (v.tooShort) message = `Gunakan sekurang-kurangnya ${input.minLength} aksara.`;
            else if (v.tooLong) message = `Gunakan tidak lebih daripada ${input.maxLength} aksara.`;
            else if (v.rangeUnderflow) message = `Masukkan nombor sekurang-kurangnya ${input.min}.`;
            else if (v.rangeOverflow) message = `Masukkan nombor tidak melebihi ${input.max}.`;
            else if (v.badInput || v.stepMismatch) message = 'Masukkan nombor yang sah.';
            else if (v.patternMismatch) message = 'Ikut format yang ditunjukkan.';
            input.setCustomValidity(message);
        });
    });
    root.querySelectorAll('audio[controls]').forEach(audio => {
        if (audio.dataset.malayAudio) return;
        audio.dataset.malayAudio = 'true';
        const box = document.createElement('div'); box.className = 'pupil-audio';
        const play = document.createElement('button'); play.type = 'button'; play.textContent = '▶ Dengar';
        const repeat = document.createElement('button'); repeat.type = 'button'; repeat.textContent = '↻ Ulang';
        const seek = document.createElement('input'); seek.type = 'range'; seek.min = '0'; seek.max = '100'; seek.step = '.1'; seek.value = '0'; seek.setAttribute('aria-label','Kedudukan audio');
        const status = document.createElement('span'); status.className = 'pupil-audio-status'; status.setAttribute('role','status'); status.textContent = 'Tekan Dengar untuk mula.';
        box.append(play, repeat, seek, status); audio.after(box); audio.controls = false;
        const visibility = () => { box.hidden = audio.hidden; };
        visibility(); new MutationObserver(visibility).observe(audio,{attributes:true,attributeFilter:['hidden']});
        const begin = async () => { try { await audio.play(); } catch { status.textContent = 'Audio belum dapat dimainkan. Cuba lagi.'; } };
        play.addEventListener('click', () => audio.paused ? begin() : audio.pause());
        repeat.addEventListener('click', () => { audio.currentTime = 0; begin(); });
        audio.addEventListener('play', () => {play.textContent = 'Ⅱ Jeda';status.textContent = 'Sedang dimainkan…';});
        audio.addEventListener('pause', () => {play.textContent = '▶ Dengar';status.textContent = audio.ended ? 'Selesai. Dengar semula jika perlu.' : 'Dijeda.';});
        audio.addEventListener('ended', () => {play.textContent = '▶ Dengar';status.textContent = 'Selesai. Dengar semula jika perlu.';});
        audio.addEventListener('timeupdate', () => {seek.value = Number.isFinite(audio.duration) && audio.duration > 0 ? String(audio.currentTime / audio.duration * 100) : '0';});
        seek.addEventListener('input', () => {if (Number.isFinite(audio.duration) && audio.duration > 0) audio.currentTime = Number(seek.value) / 100 * audio.duration;});
        audio.addEventListener('error', () => {status.textContent = 'Audio tidak tersedia. Cuba lagi nanti.';});
    });
    root.querySelectorAll('input[type=file]').forEach(input => {
        if(input.dataset.malayFile) return;
        input.dataset.malayFile = 'true'; input.classList.add('sr-only'); input.tabIndex = -1;
        const button = document.createElement('button'); button.type = 'button'; button.className = 'pupil-file-button'; button.textContent = '📁 Pilih Fail';
        const name = document.createElement('span'); name.className = 'pupil-file-name'; name.textContent = 'Belum ada fail dipilih.';
        input.after(button,name); button.addEventListener('click',()=>input.click()); input.addEventListener('change',()=>{name.textContent = input.files?.[0]?.name || 'Belum ada fail dipilih.';});
    });
}
localiseControls();
document.addEventListener('livewire:initialized', () => {
    window.Livewire.hook('morphed', () => localiseControls());
});
// A Malay confirmation dialog for content-management actions, including Livewire.
const confirmDialog = document.createElement('dialog');
confirmDialog.className = 'admin-logout-dialog';
confirmDialog.setAttribute('aria-labelledby','confirmation-title');
confirmDialog.innerHTML = '<div class="admin-logout-content"><span class="admin-logout-icon" aria-hidden="true">?</span><h2 id="confirmation-title">Sahkan tindakan</h2><p data-confirm-message></p><div class="admin-logout-buttons"><button type="button" class="admin-logout-cancel" autofocus>Batal</button><button type="button" class="admin-logout-confirm">Teruskan</button></div></div>';
document.body.append(confirmDialog);
let pendingConfirmation = null, confirmationTrigger = null;
const askConfirmation = (message, trigger, action) => {
    confirmDialog.querySelector('[data-confirm-message]').textContent = message;
    pendingConfirmation = action; confirmationTrigger = trigger;
    confirmDialog.showModal();
};
confirmDialog.querySelector('.admin-logout-cancel').addEventListener('click',()=>confirmDialog.close());
confirmDialog.addEventListener('click',event=>{if(event.target===confirmDialog)confirmDialog.close();});
confirmDialog.addEventListener('close',()=>{pendingConfirmation=null;confirmationTrigger?.focus();});
confirmDialog.querySelector('.admin-logout-confirm').addEventListener('click',()=>{const action=pendingConfirmation;confirmDialog.close();action?.();});
const approvedForms = new WeakSet(), approvedButtons = new WeakSet();
document.addEventListener('submit',event=>{
    const form=event.target;
    if(!form.matches('form[data-confirm]'))return;
    if(approvedForms.has(form)){approvedForms.delete(form);return;}
    event.preventDefault(); event.stopImmediatePropagation();
    const submitter=event.submitter;
    askConfirmation(form.dataset.confirm,submitter,()=>{approvedForms.add(form);form.requestSubmit(submitter);});
},true);
document.addEventListener('click',event=>{
    const button=event.target.closest('[wire\\:confirm]');
    if(!button)return;
    if(approvedButtons.has(button)){approvedButtons.delete(button);return;}
    event.preventDefault();event.stopImmediatePropagation();
    const message=button.getAttribute('wire:confirm');
    askConfirmation(message,button,()=>{button.removeAttribute('wire:confirm');approvedButtons.add(button);button.click();approvedButtons.delete(button);button.setAttribute('wire:confirm',message);});
},true);

// Three ordinary listens, followed by explicitly requested and recorded audio assistance.
document.querySelectorAll('[data-dictation-player]').forEach(player=>{
    const play=player.querySelector('[data-dictation-play]'),help=player.querySelector('[data-dictation-help]'),stop=player.querySelector('[data-dictation-stop]'),audio=player.querySelector('audio'),status=player.querySelector('[data-audio-status]');
    let plays=Number(player.dataset.plays),url=null;
    const listen=async assisted=>{
        play.disabled=true;if(help)help.disabled=true;status.textContent='Sedang menyediakan audio…';
        try {
            const response=await fetch(player.dataset.url,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify({question_id:Number(player.dataset.questionId),help:assisted,request_key:crypto.randomUUID()})});
            if(!response.ok){status.textContent=response.status===429?(player.dataset.strict==='1'?'Audio telah didengar tiga kali. Tulis jawapan anda.':'Tekan Bantuan Audio untuk dengar lagi.'):'Audio belum dapat dimainkan. Cuba lagi.';if(response.status===429)plays=3;return;}
            plays=Number(response.headers.get('X-Audio-Plays'));player.querySelector('[data-audio-count]').textContent=player.dataset.strict==='1' ? `${plays} / 3` : `${plays} / 3 · Bantuan ${response.headers.get('X-Audio-Help') || 0}`;
            if(url)URL.revokeObjectURL(url);url=URL.createObjectURL(await response.blob());audio.src=url;await audio.play();status.textContent=assisted?'Bantuan direkodkan. Dengar dan cuba lagi.':'Dengar dengan teliti.';
        }catch{status.textContent='Audio belum dapat dimainkan. Cuba lagi.';}finally{play.disabled=plays>=3;if(help){help.hidden=plays<3;help.disabled=false;}}
    };
    play.addEventListener('click',()=>listen(false));help?.addEventListener('click',()=>listen(true));
    stop.addEventListener('click',()=>{audio.pause();status.textContent='Audio dihentikan.';});
});

// Short decorative transition. The continuous clock is never reset here.
document.querySelectorAll('a[href*="/zon/"][href$="/mula"]').forEach(link=>link.addEventListener('click',event=>{
    if(event.ctrlKey||event.metaKey||event.shiftKey||event.altKey||event.button!==0||matchMedia('(prefers-reduced-motion: reduce)').matches)return;
    event.preventDefault();const plane=document.createElement('div');plane.className='zone-flight-transition';plane.textContent='✈';plane.setAttribute('aria-hidden','true');document.body.append(plane);setTimeout(()=>location.assign(link.href),650);
}));

const arrival=document.querySelector('[data-arrival-briefing]');
if(arrival){arrival.close();arrival.showModal();arrival.addEventListener('cancel',event=>event.preventDefault());}

// One authoritative server clock. Five-second leases exclude closed/offline gaps.
const journeyClock=document.querySelector('[data-journey-clock]');
if(journeyClock){
    let seconds=Number(journeyClock.dataset.elapsed),last=performance.now(),running=false;
    const label=journeyClock.querySelector('[data-journey-status]'),value=journeyClock.querySelector('[data-journey-time]');
    const render=()=>{const n=Math.floor(seconds+(running?(performance.now()-last)/1000:0));value.textContent=`${String(Math.floor(n/60)).padStart(2,'0')}:${String(n%60).padStart(2,'0')}`;};
    const ping=async(pause=false,beacon=false)=>{
        const payload=JSON.stringify({pause,_token:document.querySelector('meta[name="csrf-token"]').content});
        if(beacon && journeyClock.dataset.continuous==='1')return;
        if(beacon){navigator.sendBeacon(journeyClock.dataset.url,new Blob([payload],{type:'application/json'}));return;}
        try{
            const res=await fetch(journeyClock.dataset.url,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:payload,keepalive:true});
            if(!res.ok)throw Error();const data=await res.json();if(data.seconds!==null)seconds=data.seconds;last=performance.now();running=!data.paused&&!data.finished;
            label.textContent=running?'Kemajuan disimpan automatik':'Jam dijeda';render();
        }catch{if(journeyClock.dataset.continuous==='1'){label.textContent='Masa diteruskan · Tiada sambungan';running=true;return;}seconds+=running?(performance.now()-last)/1000:0;last=performance.now();running=false;label.textContent='Tiada sambungan · Jam dijeda';}
    };
    ping(document.hidden||!navigator.onLine);setInterval(()=>{if(!document.hidden&&navigator.onLine)ping();},5000);setInterval(render,1000);
    window.addEventListener('offline',()=>{if(journeyClock.dataset.continuous==='1'){label.textContent='Masa diteruskan · Tiada sambungan';return;}seconds+=running?(performance.now()-last)/1000:0;last=performance.now();running=false;label.textContent='Tiada sambungan · Jawapan disimpan pada peranti';ping(true,true);});
    window.addEventListener('online',()=>ping(document.hidden));
    document.addEventListener('visibilitychange',()=>{if(document.hidden){if(journeyClock.dataset.continuous!=='1')running=false;ping(true,true);}else if(navigator.onLine)ping();});
    window.addEventListener('pagehide',()=>ping(true,true));
}

// Drafts are private to the current question. The server rechecks ownership and the current step.
document.querySelectorAll('[data-draft-url]').forEach(form=>{
    const questionId=form.querySelector('[name="question_id"]').value;
    const key=`dibitara-draft:${form.dataset.draftUrl}:${questionId}`;
    const status=form.querySelector('[data-draft-status]');let timer,submitting=false,restoring=true;
    const read=()=>{
        const data=new FormData(form),plain=data.get('answer');
        if(plain!==null)return plain;
        const array=data.getAll('answer[]');if(array.length)return array;
        const object={};for(const [name,value] of data){const match=name.match(/^answer\[([^\]]+)\]$/);if(match)object[match[1]]=value;}return object;
    };
    const store=()=>{const answer=read();try{localStorage.setItem(key,JSON.stringify({value:answer}));}catch{}return answer;};
    const save=async()=>{
        if(submitting)return;const answer=store();
        if(!navigator.onLine){status.textContent='Tiada sambungan. Jawapan disimpan pada peranti.';return;}
        try{const res=await fetch(form.dataset.draftUrl,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({question_id:Number(questionId),answer}),keepalive:true});if(!res.ok)throw Error();status.textContent='✓ Jawapan disimpan.';}catch{status.textContent='Jawapan disimpan pada peranti. Menunggu sambungan.';}
    };
    let draft;try{draft=JSON.parse(localStorage.getItem(key)||form.dataset.draft||'null')?.value;}catch{}
    if(draft!==undefined&&draft!==null){
        if(Array.isArray(draft)&&form.querySelector('[data-order-answer]')){
            for(const id of draft){[...form.querySelectorAll('[data-token]')].find(t=>t.dataset.token===String(id))?.click();}
        }else{
            for(const input of form.querySelectorAll('[name="answer"],[name^="answer["]')){
                const match=input.name.match(/^answer\[([^\]]+)\]$/);const v=match?draft[match[1]]:draft;
                if(v===undefined)continue;if(input.type==='radio')input.checked=input.value===String(v);else input.value=String(v);
                if(input.tagName==='SELECT'||input.matches('[data-category-value]'))input.dispatchEvent(new Event('change',{bubbles:true}));
            }
        }
        status.textContent='✓ Jawapan terdahulu dipulihkan.';
    }

    restoring=false;
    const changed=()=>{if(restoring||submitting)return;store();status.textContent='Menyimpan…';clearTimeout(timer);timer=setTimeout(save,700);};
    form.addEventListener('input',changed);form.addEventListener('change',changed);form.addEventListener('click',e=>{if(e.target.closest('[data-token],[data-order-reset]'))changed();});form.addEventListener('drop',changed);
    form.addEventListener('submit',e=>{if(!navigator.onLine){e.preventDefault();store();status.textContent='Sambung internet untuk menghantar jawapan.';form.querySelector('.mission-submit').textContent='Hantar Jawapan ›';return;}if(form.checkValidity()&&!e.defaultPrevented){submitting=true;clearTimeout(timer);try{localStorage.removeItem(key);}catch{}}});
    window.addEventListener('online',save);window.addEventListener('pagehide',()=>{if(!submitting)save();});

});

const journeyTransition=document.querySelector('[data-adventure-transition]');
if(journeyTransition&&!matchMedia('(prefers-reduced-motion: reduce)').matches){setTimeout(()=>{const plane=document.createElement('div');plane.className='zone-flight-transition';plane.textContent='✈';plane.setAttribute('aria-hidden','true');document.body.append(plane);},1100);setTimeout(()=>location.assign(journeyTransition.dataset.next),1900);}
// Recheck the current journey after a browser Back restoration.
window.addEventListener('pageshow',event=>{if(event.persisted&&document.body.classList.contains('student-top-layout'))location.reload();});
