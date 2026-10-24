const formatTime = seconds => `${String(Math.floor(seconds / 60)).padStart(2, '0')} minit ${String(seconds % 60).padStart(2, '0')} saat`;

document.querySelectorAll('[data-zone-timer]').forEach(timer => {
    const initial = Number(timer.dataset.elapsed);
    const started = performance.now();
    const value = timer.querySelector('[data-timer-value]');
    const update = () => { value.textContent = formatTime(initial + Math.floor((performance.now() - started) / 1000)); };
    update();
    setInterval(update, 1000);
});

const countdown = document.querySelector('[data-zone-countdown]');
if (countdown) {
    const seconds = Number(countdown.dataset.seconds);
    const form = countdown.querySelector('[data-countdown-form]');
    const number = countdown.querySelector('[data-countdown-number]');
    countdown.close();
    countdown.showModal();
    countdown.addEventListener('cancel', event => event.preventDefault());
    const began = performance.now();
    let sent = false;
    const tick = () => {
        const remaining = Math.max(0, seconds - Math.floor((performance.now() - began) / 1000));
        const label = remaining ? String(remaining) : 'Mula';
        if (number.textContent !== label) {
            number.textContent = label;
            number.classList.remove('countdown-pop');
            void number.offsetWidth;
            number.classList.add('countdown-pop');
        }
        if (!remaining && !sent) {
            sent = true;
            // Briefly show “Mula”; the authoritative clock starts on this POST.
            setTimeout(() => { form.requestSubmit(); }, seconds ? 300 : 0);
        }
    };
    tick();
    const interval = setInterval(() => { tick(); if (sent) clearInterval(interval); }, 80);
}
