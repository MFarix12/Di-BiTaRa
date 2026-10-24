// The native modal supplies focus trapping and makes the page behind it inert.
// Four shaded panels leave the actual target visible, without copying its content.
function mountTour(root) {
    if (root.tourMounted) return;
    root.tourMounted = true;
    const dialog = root.querySelector('dialog');
    const card = root.querySelector('.guided-tour-card');
    const spotlight = root.querySelector('.guided-tour-spotlight');
    const hint = root.querySelector('.guided-tour-mobile-hint');
    let step = null;
    let frame = 0;
    let savedFocus = null;
    let savedOverflow = '';
    const rectStyle = (element, x, y, width, height) => Object.assign(element.style, {
        left: `${x}px`, top: `${y}px`, width: `${Math.max(0,width)}px`, height: `${Math.max(0,height)}px`,
    });
    function targetElement() {
        let target = document.querySelector(`[data-tour="${root.dataset.target}"]`);
        if (root.dataset.target === 'zones' && window.innerWidth <= 650) {
            target = target?.querySelector('.fd-zone-card-title') ?? target;
        }
        const hidden = !target || !target.getClientRects().length || target.getBoundingClientRect().width === 0;
        hint.hidden = !hidden;
        if (hidden) target = document.querySelector('[data-tour="mobile-menu"]');
        return target;
    }
    function position(scroll = false) {
        if (!dialog.open) return;
        const target = targetElement();
        const w = window.innerWidth, h = window.innerHeight, gap = 16;
        card.style.width = `${Math.min(360,w-24)}px`;
        const ch = Math.min(card.offsetHeight,h-24), cw = card.offsetWidth;
        card.style.maxHeight = `${h-24}px`;
        let box = target?.getBoundingClientRect();
        const header = document.querySelector('.student-navbar');
        const headerBottom = Math.max(0,header?.getBoundingClientRect().bottom ?? 0);
        if (scroll && target && !target.closest('.student-navbar')) {
            // Leave room for both the target and the card, especially on a phone.
            const top = Math.min(headerBottom+24,Math.max(12,h-ch-120));
            window.scrollBy({top:box.top-top,behavior:'instant'});
            box = target.getBoundingClientRect();
        }
        if (!box || box.width === 0) {
            box = {left:w/2,top:headerBottom+12,right:w/2,bottom:headerBottom+12};
        }
        const x = Math.max(5,box.left-6), y = Math.max(5,box.top-6);
        const right = Math.min(w-5,box.right+6), bottom = Math.min(h-5,box.bottom+6);
        rectStyle(spotlight,x,y,right-x,bottom-y);
        rectStyle(root.querySelector('.shade-top'),0,0,w,y);
        rectStyle(root.querySelector('.shade-left'),0,y,x,bottom-y);
        rectStyle(root.querySelector('.shade-right'),right,y,w-right,bottom-y);
        rectStyle(root.querySelector('.shade-bottom'),0,bottom,w,h-bottom);
        let cardX = Math.min(w-cw-12,Math.max(12,x));
        let cardY;
        if (bottom+gap+ch <= h-12) cardY = bottom+gap;
        else if (y-gap-ch >= 12) cardY = y-gap-ch;
        else if (right+gap+cw <= w-12) { cardX=right+gap; cardY=Math.max(12,Math.min(h-ch-12,y)); }
        else { cardY=h-ch-12; }
        Object.assign(card.style,{left:`${cardX}px`,top:`${Math.max(12,cardY)}px`});
    }
    function queuePosition() {
        cancelAnimationFrame(frame);
        frame=requestAnimationFrame(()=>position());
    }
    function sync() {
        if (root.dataset.active === '1') {
            if (!dialog.open) {
                savedFocus = document.activeElement;
                savedOverflow=document.body.style.overflow;
                document.body.style.overflow='hidden';
                dialog.showModal();
            }
            const changed = step !== root.dataset.step;
            step=root.dataset.step;
            requestAnimationFrame(()=>{
                position(changed);
                if (changed) root.querySelector('[data-tour-next]')?.focus({preventScroll:true});
            });
        } else if (dialog.open) {
            dialog.close();
            document.body.style.overflow=savedOverflow;
            const focus = savedFocus && savedFocus !== document.body && savedFocus.isConnected ? savedFocus : document.querySelector('[data-tour="profile"]');
            focus?.focus({preventScroll:true});
        }
    }
    dialog.addEventListener('cancel',event=>{
        event.preventDefault();
        root.querySelector('[data-tour-dismiss]')?.click();
    });
    // Clicking the dim area never navigates away from the guide accidentally.
    const observer=new MutationObserver(sync);
    observer.observe(root,{attributes:true,attributeFilter:['data-active','data-step','data-target']});
    window.addEventListener('resize',()=>requestAnimationFrame(()=>position(true)));
    window.addEventListener('scroll',queuePosition,true);
    window.visualViewport?.addEventListener('resize',queuePosition);
    // Reposition once fonts have settled, including the first page load.
    document.fonts?.ready.then(queuePosition);
    sync();
}
function mountTours() { document.querySelectorAll('[data-guided-tour]').forEach(mountTour); }
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded',mountTours,{once:true});
else mountTours();
document.addEventListener('livewire:navigated',mountTours);
