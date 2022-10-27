interface ScrollKeys {
    [key: number]: number
}

let keys: ScrollKeys = {
    37: 1,
    38: 1,
    39: 1,
    40: 1
};

export function preventDefault(e:any) {
    e.preventDefault();
}

export function preventDefaultForScrollKeys(e: KeyboardEvent) {
    if (keys[e.keyCode]) {
        e.preventDefault();
        return false;
    }
}

// modern Chrome requires { passive: false } when adding event
let supportsPassive = false;
try {
    // @ts-ignore
    window.addEventListener("test", null, Object.defineProperty({}, 'passive', {
        get: function () {
            supportsPassive = true;
        }
    }));
} catch (e) {}

export const wheelOpt = supportsPassive ? {
    passive: false
} : false;

export const wheelEvent = 'onwheel' in document.createElement('div') ? 'wheel' : 'mousewheel';

