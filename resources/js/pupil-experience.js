// Lightweight feedback and connection states, scoped to learner sessions.
const pupilConnection=document.querySelector('[data-pupil-connection]');
if(pupilConnection){
 const update=()=>{pupilConnection.hidden=navigator.onLine;};update();
 addEventListener('online',update);addEventListener('offline',update);
 document.querySelectorAll('audio').forEach(audio=>{
  audio.addEventListener('error',()=>{
   let message=audio.parentElement.querySelector('[data-audio-failure]');
   if(!message){message=document.createElement('p');message.dataset.audioFailure='1';message.className='notice error';message.setAttribute('role','status');audio.after(message);}
   message.textContent='🎧 Audio belum dapat dimainkan. Semak internet, kemudian cuba Dengar semula.';
  });
 });
 document.querySelectorAll('form[data-mission-question]').forEach(form=>{
  form.addEventListener('submit',event=>{
   if(event.defaultPrevented||!navigator.onLine||!form.checkValidity())return;
   form.setAttribute('aria-busy','true');
   const button=form.querySelector('[type=submit],.mission-submit');
   if(button)setTimeout(()=>{button.disabled=true;button.textContent='Menyimpan jawapan…';},0);
  });
 });
 addEventListener('pageshow',()=>document.querySelectorAll('form[aria-busy=true]').forEach(form=>{form.removeAttribute('aria-busy');const b=form.querySelector('.mission-submit');if(b){b.disabled=false;b.textContent='Hantar Jawapan ›';}}));
}

document.querySelectorAll('.pupil-instruction-audio').forEach(box=>{
 const button=box.querySelector('button'),audio=box.querySelector('audio'),status=box.querySelector('[data-instruction-status]');
 button.addEventListener('click',async()=>{if(!audio.paused){audio.pause();return;}try{status.textContent='Memuatkan arahan…';await audio.play();}catch{status.textContent='Audio belum tersedia. Baca arahan di atas dan cuba lagi nanti.';}});
 audio.addEventListener('play',()=>{button.textContent='Ⅱ Jeda Arahan';status.textContent='Dengar arahan, kemudian cuba.';});
 audio.addEventListener('pause',()=>{button.textContent='🎧 Dengar Arahan';});
 audio.addEventListener('ended',()=>{status.textContent='Jom cuba!';});
 audio.addEventListener('error',()=>{status.textContent='Audio belum tersedia. Baca arahan di atas dahulu.';});
});
