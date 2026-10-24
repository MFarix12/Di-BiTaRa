// One owner for background music. Page content sends commands; it never creates another player.
export class SessionMusic {
    constructor(config, notify=()=>{}, storage=sessionStorage, AudioType=Audio) {
        this.config=config;this.notify=notify;this.storage=storage;this.key=`dibitara:music:${config.session}`;
        let saved={};try{saved=JSON.parse(storage.getItem(this.key)||'{}')}catch{}
        this.muted=saved.muted===true;this.blocked=false;this.stopped=false;this.ducked=false;this.cueActive=false;
        this.music=new AudioType(config.src);this.music.loop=true;this.music.preload='auto';this.music.volume=.22;
        this.music.addEventListener('loadedmetadata',()=>{if(saved.position && Number.isFinite(this.music.duration))this.music.currentTime=saved.position%this.music.duration;},{once:true});
        this.music.addEventListener('error',()=>{this.blocked=true;this.publish()});
        this.sounds={beep:new AudioType(config.beep),start:new AudioType(config.start)};
        Object.values(this.sounds).forEach(s=>{s.volume=.28;s.preload='auto';s.onended=s.onerror=()=>{this.cueActive=false;this.volume()}});
    }
    publish(){this.notify({muted:this.muted,audible:!this.muted&&!this.music.paused&&!this.stopped,blocked:this.blocked})}
    save(){try{this.storage.setItem(this.key,JSON.stringify({muted:this.muted,position:this.music.currentTime||0}))}catch{}}
    volume(){this.music.volume=this.ducked||this.cueActive ? .025:.22}
    async start(){if(this.stopped||this.muted){this.publish();return}try{await this.music.play();if(this.muted||this.stopped)this.music.pause();this.blocked=false}catch{this.blocked=true}this.publish()}
    toggle(){if(this.muted||this.music.paused){this.muted=false;this.stopped=false;this.start()}else{this.muted=true;this.music.pause();this.publish()}this.save()}
    foreground(active){this.ducked=active;this.volume()}
    countdown(remaining){const sound=remaining?this.sounds.beep:this.sounds.start;this.cueActive=true;this.volume();sound.currentTime=0;sound.play().catch(()=>{this.cueActive=false;this.volume()})}
    stop(clear=false){this.save();this.stopped=true;this.music.pause();this.music.currentTime=0;Object.values(this.sounds).forEach(s=>s.pause());if(clear){try{this.storage.removeItem(this.key)}catch{}}this.publish()}
}
