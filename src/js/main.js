const header = document.querySelector("header");
const nav = document.querySelector("nav");

if (header && nav) {
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    let updateQueued = false;

    const updateNav = () => {
        const headerEnd = header.offsetTop + header.offsetHeight - 1;
        const passedHeader = window.scrollY >= headerEnd;

        if (passedHeader) {
            nav.classList.remove("leaving");
            nav.classList.add("scroll");
        } else if (nav.classList.contains("scroll")) {
            if (reducedMotion.matches) {
                nav.classList.remove("scroll", "leaving");
            } else {
                nav.classList.add("leaving");
            }
        }

        updateQueued = false;
    };

    const queueUpdate = () => {
        if (!updateQueued) {
            updateQueued = true;
            requestAnimationFrame(updateNav);
        }
    };

    nav.addEventListener("animationend", (event) => {
        if (event.target === nav && event.animationName === "rise") {
            nav.classList.remove("scroll", "leaving");
        }
    });

    updateNav();
    window.addEventListener("scroll", queueUpdate, { passive: true });
    window.addEventListener("resize", queueUpdate);
}

const experiences = document.querySelectorAll("time.experience");

if (experiences.length) {
    const year = 365.2425 * 24 * 60 * 60 * 1000;

    const updateExperiences = () => {
        const now = Date.now();

        for (const experience of experiences) {
            const start = new Date(experience.dateTime).getTime();
            const factor = Number(experience.dataset.factor ?? 1);
            const years = ((now - start) / year) * factor;

            experience.textContent = `${years.toPrecision(11)} Yrs`;
        }
    };

    updateExperiences();
    window.setInterval(updateExperiences, 100);
}