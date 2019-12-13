class FAQ {
    constructor(element, index) {
        this.index = index;
        this.id = index;
        this.element = element;
        this.active = false;
        this.eventNames = [
            'faq:click',
            'faq:beforeActivate',
            'faq:afterActivate',
        ];
        this.eventObjs = [];
        this.originalHeight = element.offsetHeight;

        this.setup();
        this.listen();
    }

    setup() {
        this.handleImages();
        this.handleIframes();

        this.events();
    }

    handleImages() {
        var images = this.element.querySelectorAll('img');

        images.forEach(img => {
            img.parentElement.setAttribute('data-fancybox', '');
            jQuery(img.parentElement).fancybox();

        });
    }

    handleIframes() {
        var youtubeFrames = this.element.querySelectorAll('iframe');

        youtubeFrames.forEach(frame => {
            var fancyboxTrigger = document.createElement('a');

            fancyboxTrigger.setAttribute('data-fancybox', '');
            fancyboxTrigger.setAttribute('href', frame.src);

            frame.before(fancyboxTrigger);

            jQuery(fancyboxTrigger).fancybox();

            console.log(frame)
        });
    }

    events() {
        this.eventNames.forEach((name) => {
            const event = new CustomEvent(name, {
                bubbles: true,
                detail: {
                    instance: this
                }
            });
            const item = {};

            item[name] = event;

            this.eventObjs.push(item);
        });
    }

    getEvent(name) {
        let event = null;

        this.eventObjs.forEach(e => {
            var key = Object.keys(e)[0];

            if (name === key) event = Object.values(e)[0]
        });

        return event;
    }

    listen() {
        this.element.querySelector('.faq__question').addEventListener('click', () => {
            if (this.active) {
                this.deactivate();
            } else {
                this.emit('faq:beforeActivate');

                this.activate();

                this.emit('faq:afterActivate');
            }

            this.emit('faq:click');
        });
    }

    getExpandedHeight() {
        const questionHeight = this.element.querySelector('.faq__answer').offsetHeight;

        return questionHeight + (this.originalHeight);
    }

    activate() {
        this.element.classList.add('faq--active');

        $(this.element).animate({
            height: this.getExpandedHeight()
        });

        this.active = true;
    }

    deactivate() {
        this.element.classList.remove('faq--active');

        $(this.element).animate({
            height: this.originalHeight
        });

        this.active = false;
    }

    emit(name) {
        const event = this.getEvent(name);

        this.element.dispatchEvent(event);
    }
}

class FAQS {
    constructor() {
        this.collection = [];
        this.element = document.querySelector('.faqs');

        this.setup();
        this.listen();
    }

    setup() {
        var els = document.querySelectorAll('.faqs .faqs__item');

        els.forEach((element, index) => {
            this.collection.push(new FAQ(element, index));
        });
    }

    listen() {
        // this.element.addEventListener('faq:beforeActivate', (event) => {
        //     const instance = event.detail.instance;

        //     // this.collection.forEach(ins => {
        //     //     if (ins.id !== instance.id) {
        //     //         ins.deactivate();
        //     //     }
        //     // });
        // });

        // this.element.addEventListener('faq:click', (event) => {
        //     console.log(this)
        //     console.log(event.detail)
        // });
    }
}


new FAQS();