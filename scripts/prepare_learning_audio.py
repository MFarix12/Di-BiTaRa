"""Build the initial Malay dictation audio; runtime playback needs no TTS service."""
import asyncio
import sys
from pathlib import Path
root = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(root / '.cache' / 'audio-tools'))
import edge_tts

async def main():
    folder = root / 'storage/app/private/starter-audio'
    folder.mkdir(parents=True, exist_ok=True)
    texts = ['Roti itu dimakan di kantin.', 'Murid-murid berkumpul di sekolah.',
             'Karangan ditulis oleh Kapten Adam.', 'Kami berkumpul di dewan sekolah.',
             'Kami bermain bola di padang.']
    for i, text in enumerate(texts, 1):
        target = folder / f'imlak-{i}.mp3'
        if target.exists() and target.stat().st_size > 1000:
            continue
        await edge_tts.Communicate(text, 'ms-MY-YasminNeural', rate='-15%').save(str(target))
        print(f'Audio Imlak {i} tersedia.', flush=True)

asyncio.run(main())
