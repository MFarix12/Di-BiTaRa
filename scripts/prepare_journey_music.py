"""Original gentle Di-BITaRa adventure loop and short countdown cues (no third-party recordings)."""
import math, wave, pathlib
import numpy as np
root=pathlib.Path(__file__).resolve().parents[1]/'public/audio/journey';root.mkdir(parents=True,exist_ok=True)
sr=22050

def save(name,a):
    with wave.open(str(root/name),'wb') as f:
        f.setparams((1,2,sr,0,'NONE','not compressed'));f.writeframes((np.clip(a,-.8,.8)*32767).astype('<i2').tobytes())

def note(a,start,midi,duration,volume):
    n=int(duration*sr);t=np.arange(n)/sr;freq=440*2**((midi-69)/12)
    env=np.minimum(t/.018,1)*np.minimum((duration-t)/.06,1)*np.exp(-t*2.5)
    tone=(np.sin(2*np.pi*freq*t)+.18*np.sin(2*np.pi*freq*2*t))*env*volume
    k=int(start*sr);end=min(k+n,len(a));a[k:end]+=tone[:end-k]

music=np.zeros(sr*32)
melodies=[[72,76,79,76,74,76,79,83],[69,72,76,72,71,72,76,79],[65,69,72,69,67,69,72,76],[67,71,74,71,69,71,74,79]]
for bar in range(16):
    chord=bar%4
    for i,pitch in enumerate(melodies[chord]):
        if i in [3,7]:continue
        note(music,bar*2+i*.25,pitch,.32,.11)
    for beat in range(4):note(music,bar*2+beat*.5,[48,45,41,43][chord]+(12 if beat%2 else 0),.4,.075)
save('adventure-loop.wav',music)
beep=np.zeros(int(sr*.19));note(beep,0,79,.16,.18);save('countdown-beep.wav',beep)
go=np.zeros(int(sr*.28));note(go,0,79,.22,.15);note(go,.055,84,.22,.13);save('countdown-start.wav',go)
print('Original loop: 32 seconds; 2 short countdown cues generated.')
