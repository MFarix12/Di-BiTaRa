import asyncio,sys
from pathlib import Path
root=Path(__file__).resolve().parents[1]
sys.path.insert(0,str(root/'.cache/audio-tools'))
import edge_tts
texts={'true_false':'Pilih Betul atau Salah. Kemudian tekan Hantar Jawapan.','drag_drop':'Pilih satu kad. Kemudian pilih kategori yang sesuai. Anda juga boleh tarik dan lepas.','sentence_order':'Pilih perkataan satu demi satu untuk membina ayat. Kemudian tekan Hantar Jawapan.','fill_blank':'Pilih ejaan yang betul. Kemudian salin ayat lengkap dan tekan Hantar Jawapan.','dictation':'Tekan Dengar. Tulis ayat yang anda dengar. Kemudian tekan Hantar Jawapan.','objective':'Pilih jawapan yang betul. Kemudian tekan Hantar Jawapan.'}
async def main():
 folder=root/'public/audio/instructions';folder.mkdir(parents=True,exist_ok=True)
 for name,text in texts.items():
  p=folder/(name+'.mp3')
  if not p.exists():await edge_tts.Communicate(text,'ms-MY-YasminNeural',rate='-10%').save(str(p))
  print(name,p.stat().st_size,flush=True)
asyncio.run(main())
