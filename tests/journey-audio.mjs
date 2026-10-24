import {SessionMusic} from '../resources/js/session-music.js';
import assert from 'node:assert/strict';
const store=new Map();const storage={getItem:k=>store.get(k),setItem:(k,v)=>store.set(k,v),removeItem:k=>store.delete(k)};
const instances=[];
class FakeAudio extends EventTarget{constructor(src){super();this.src=src;this.paused=true;this.currentTime=0;this.duration=32;instances.push(this)}play(){this.paused=false;return Promise.resolve()}pause(){this.paused=true}}
let state;const player=new SessionMusic({session:'qa-1',src:'music',beep:'beep',start:'start'},s=>state=s,storage,FakeAudio);
await player.start();assert.equal(state.audible,true);assert.equal(instances.length,3);
player.music.currentTime=12;
// Page-ready changes do not construct an audio object, reset playback or seek.
player.foreground(false);player.publish();assert.equal(player.music.currentTime,12);assert.equal(instances.length,3);
player.foreground(true);assert.equal(player.music.volume,.025);player.foreground(false);assert.equal(player.music.volume,.22);
player.toggle();assert.equal(state.muted,true);assert.equal(player.music.paused,true);player.publish();assert.equal(state.muted,true);
player.countdown(3);assert.equal(player.sounds.beep.paused,false);assert.equal(player.music.volume,.025);player.sounds.beep.onended();assert.equal(player.music.volume,.22);
player.toggle();await new Promise(setImmediate);assert.equal(state.audible,true);assert.equal(player.music.currentTime,12);
player.save();const resumed=new SessionMusic(player.config,()=>{},storage,FakeAudio);resumed.music.dispatchEvent(new Event('loadedmetadata'));assert.equal(resumed.music.currentTime,12);
player.stop(true);assert.equal(player.music.paused,true);assert.equal(player.music.currentTime,0);assert.equal(store.has(player.key),false);
console.log('PASS: one persistent player, position unchanged across page-ready, mute persistence, ducking, countdown, refresh resume, session stop.');
