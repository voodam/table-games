/*
 * Primitives and functional programming
 */

const mapPairs = (fstCb, sndCb, arr) => arr.map(([fst, snd]) => [fstCb(fst), sndCb(snd)]);
const id = val => val;
const _new = (cls) => (...args) => new cls(...args);
const argsArrayToRest = (f) => (args) => f(...args);
const noop = () => {};
const toArray = value => Array.isArray(value) ? value : [value];
class NotImplemented extends Error {}

const mapDict = (callbacks, dict) => {
    const newDict = {};
    for (const [key, value] of Object.entries(dict)) {
        const cbk = callbacks[key] || id;
        newDict[key] = cbk(value);
    }
    return newDict;
};

const curry = (func) => {
    const curried = (...args) => {
        if (args.length >= func.length) {
            return func.apply(this, args);
        } else {
            return (...args2) => {
                return curried.apply(this, args.concat(args2));
            };
        }
    };
    
    return curried;
};

const repeat = (times, cbk) => {
    for (let i = 0; i < times; i++) {
        cbk();
    }
};

/*
 * Strings
 */

const nextChar = (c) => {
    return String.fromCharCode(c.charCodeAt(0) + 1);
};

const camelCasetoHyphen = str => str.replace(/[A-Z]/g, match => '-' + match.toLowerCase());

const format = (str, ...args) => {
    const replacer = (match, number) => typeof args[number] !== 'undefined' ? args[number] : match;
    return str.replace(/{(\d+)}/g, replacer);
};

const ucfirst = str => {
    const firstChar = str[0].toUpperCase();
    return firstChar + str.substring(1);
};

/*
 * DOM and HTML
 */

const createTable = ([numRows, numCells], table = undefined) => {
    if (!table) {
        table = document.createElement('table');
    }
    repeat(numRows, () => {
        const tr = document.createElement('tr');
        repeat(numCells, () => {
            const td = document.createElement('td');
            tr.appendChild(td);
        });
        table.appendChild(tr);
    });

    return table;
};

const createElement = (tagName = 'div', textContent = '', classes = []) => {
    const elem = document.createElement(tagName);
    elem.textContent = textContent;
    for (const c of toArray(classes)) {
        elem.classList.add(c);
    }
    return elem;
};

const appendChildren = (parent, children, clearParent = false) => {
    console.assert(parent instanceof Node);
    
    if (clearParent) {
        clearElement(parent);
    }
    for(const child of toArray(children)) {
        parent.appendChild(child);
    }
};

const assignElement = (target, source) => {
    for (const c of source.classList) {
        target.classList.add(c);
    }
    
    target.innerHTML = source.innerHTML;
};

const createElemsFromStr = (html) => {
      const div = document.createElement('div');
      div.innerHTML = html.trim();
      return Array.from(div.children);
};

const toggleDisabled = elem => {
    if (typeof elem.disabled !== 'boolean') throw new Error('Element has no "disabled" behaviour');
    elem.disabled = !elem.disabled;
};

const toggleDisplay = elem => {
    if (Style.getComputed(elem, 'display') !== 'none') {
        Style.replace(elem, 'display', 'none');
    } else {
        Style.returnBack(elem, 'display') || show(elem);
    };
};

const displayBetweenSiblings = elem => {
    Array.from(elem.parentNode.children).forEach(hide);
    show(elem);
};

const listenOnce = (elem, eventType, handler) => {
    const fullHandler = (...args) => {
        const remove = handler(...args);
        if (remove !== false) {
            elem.removeEventListener(eventType, fullHandler);
        }
    };
    elem.addEventListener(eventType, fullHandler);
};

const beforeUnload = () => {
    window.addEventListener('beforeunload', event => {
        event.preventDefault();
        event.returnValue = '';
    });
};

class Style {
    static replace(elem, attr, newVal) {
        elem.dataset[this._getDataKey(attr)] = this.getComputed(elem, attr);
        elem.style[attr] = newVal;
    }

    static returnBack(elem, attr) {
        const data = elem.dataset[this._getDataKey(attr)];
        if (!data) {
            return false;
        }

        elem.style[attr] = data;
        delete elem.dataset[this._getDataKey(attr)];
        return true;
    }

    static eq(elem, attr, value) {
        const wrapper = this._getWrapper();
        const tester = document.createElement(elem.tagName);
        wrapper.appendChild(tester);
        tester.style[attr] = value;
        const equal = this.getComputed(elem, attr) === this.getComputed(tester, attr);
        wrapper.removeChild(tester);
        return equal;
    }

    static getComputed(elem, attr) {
        return getComputedStyle(elem)[camelCasetoHyphen(attr)] || elem.style[attr];
    }

    static _getWrapper(id = 'style-helper-wrapper') {
        let wrapper = document.querySelector(`#${id}`);
        if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.id = id;
            hide(wrapper);
            document.documentElement.appendChild(wrapper);
        }

        return wrapper;
    }

    static _getDataKey(attr) {
        return `prev${ucfirst(attr)}`;
    }
}

class StyleReplaceException extends Error {}

const show = elem => elem.style.display = 'block';
const hide = elem => elem.style.display = 'none';
const clearElement = (element) => element.textContent = '';

/*
 * Misc
 */

class Debug {
    static init() {
        if (!Debug.enabled()) {
            console.log('silent mode');
            console.log = noop;
            console.assert = noop;
        }
    }
    
    static enabled() {
        return new URL(location).searchParams.has('debug');
    }
}
