// Same-origin bridge to the persistent session player. No audio instances live in changing pages.
const control=document.querySelector('[data-journey-music-toggle]');
const framed=window.parent!==window && window.frameElement?.hasAttribute('data-session-frame');
if(framed){
    const send=(command,extra={})=>window.parent.postMessage({type:'dibitara:session-audio',command,session:control?.dataset.session,...extra},location.origin);
    if(!control)send('leave',{url:location.href});
    else {
        const foreground=new Set();
        window.addEventListener('message',event=>{
            if(event.origin!==location.origin||event.source!==window.parent||event.data?.type!=='dibitara:music-state')return;
            const state=event.data.state;const label=state.audible?'Senyapkan muzik latar':'Hidupkan muzik latar';
            control.querySelector('[data-music-icon]').textContent=state.audible?'🔊':'🔇';
            control.setAttribute('aria-label',label);control.setAttribute('aria-pressed',String(state.audible));
            control.title=state.blocked?'Tekan untuk hidupkan muzik latar':label;
        });
        control.addEventListener('click',()=>send('toggle'));
        document.addEventListener('pointerdown',event=>{if(!control.contains(event.target))send('unlock')});
        document.addEventListener('keydown',event=>{if(!control.contains(event.target))send('unlock')});
        document.addEventListener('play',event=>{if(event.target instanceof HTMLMediaElement){foreground.add(event.target);send('foreground',{active:true})}},true);
        ['pause','ended','error','emptied'].forEach(type=>document.addEventListener(type,event=>{foreground.delete(event.target);send('foreground',{active:foreground.size>0})},true));
        window.addEventListener('dibitara:countdown',event=>send('countdown',{remaining:event.detail.remaining}));
        document.addEventListener('submit',event=>{if(new URL(event.target.action,location.href).pathname==='/sesi/tamat'&&event.target.checkValidity())send('stop')});
        // Clear stale foreground audio when a page leaves, but keep the music playing.
        window.addEventListener('pagehide',()=>send('foreground',{active:false}));
        send('ready',{url:location.href,title:document.title});
    }
}
