"""Generate the bundled Malay audio for the latest user-supplied PDFs (optional build task)."""
import asyncio
import sys
from pathlib import Path
root = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(root / '.cache/audio-tools'))
import edge_tts
TEXTS = {
    'imlak-1': 'di rumah',
    'imlak-2': 'bagai dihiris sembilu',
    'imlak-3': 'ukur baju di badan sendiri',
    'taklimat': 'Imbuhan di ditulis rapat dengan kata kerja pasif. Contohnya, ditulis, dimasak dan dihantar. Kata sendi di ditulis terpisah daripada kata nama tempat. Contohnya, di sekolah, di rumah dan di padang.',
}
async def main():
    folder = root / 'resources/audio/latest-20261022'
    folder.mkdir(parents=True, exist_ok=True)
    for name, text in TEXTS.items():
        target = folder / (name + '.mp3')
        if not target.exists():
            await edge_tts.Communicate(text, 'ms-MY-YasminNeural', rate='-15%').save(str(target))
        print(f'{name}: tersedia', flush=True)
if __name__ == '__main__':
    asyncio.run(main())
