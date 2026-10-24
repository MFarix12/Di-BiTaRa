"""Build the exact Malay dictation audio from the supplied Year 5/6 PDFs."""
import asyncio, json, sys
from pathlib import Path
root=Path(__file__).resolve().parents[1]
sys.path.insert(0,str(root/'.cache/audio-tools'))
import edge_tts
async def main():
 for year in (5,6):
  data=json.loads((root/f'resources/curriculum/tahun-{year}.json').read_text(encoding='utf-8'))
  folder=root/f'resources/audio/tahun-{year}'
  folder.mkdir(parents=True,exist_ok=True)
  for i,text in enumerate(data['imlak'],1):
   target=folder/f'imlak-{i}.mp3'
   if not target.exists() or target.stat().st_size<1000:
    await edge_tts.Communicate(text,'ms-MY-YasminNeural',rate='-15%').save(str(target))
   print(f'Tahun {year} imlak {i}: {target.stat().st_size} bytes',flush=True)
asyncio.run(main())
