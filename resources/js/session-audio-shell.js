import {SessionMusic} from './session-music';
const root=document.querySelector('[data-session-audio-shell]');
const frame=document.querySelector('[data-session-frame]');
if(root&&frame){
    const send=state=>frame.contentWindow?.postMessage({type:'dibitara:music-state',state},location.origin);
    const player=new SessionMusic(root.dataset,send);
    player.music.hidden=true;player.music.setAttribute('data-persistent-session-music','');root.append(player.music);
    let ended=false;
    window.addEventListener('message',event=>{
        if(event.origin!==location.origin||event.source!==frame.contentWindow)return;
        const data=event.data;if(!data||data.type!=='dibitara:session-audio')return;
        if(data.command==='leave'){
            let destination;try{destination=new URL(data.url,location.origin)}catch{return}
            if(destination.origin!==location.origin)return;
            ended=true;player.stop(true);location.replace(destination.href);return;
        }
        if(data.session!==root.dataset.session||ended)return;
        if(data.command==='ready'){
            player.foreground(false);player.stopped=false;
            let url;try{url=new URL(data.url)}catch{return}
            if(url.origin===location.origin){history.replaceState(null,'',url.href);document.title=data.title||'Pengembaraan | Di-BITaRa'}
            // play() is only needed when paused; normal navigation never restarts the player.
            if(player.music.paused)player.start();else player.publish();
        }
        if(data.command==='toggle')player.toggle();
        if(data.command==='unlock'&&player.music.paused&&!player.muted)player.start();
        if(data.command==='foreground')player.foreground(!!data.active);
        if(data.command==='countdown')player.countdown(Number(data.remaining));
        if(data.command==='stop')player.stop();
    });
    window.addEventListener('pagehide',()=>{player.save();player.music.pause()});
    setInterval(()=>player.save(),2000);
}
