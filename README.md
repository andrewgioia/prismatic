# Prismatic

Marketing website for [Prismatic](https://prismatichq.com), a software and game company founded by Michael and Andrew Gioia.

The site is built with minimal HTML, CSS, and JS. Vite handles local development and production builds.


## Project structure

```text
.
├── index.html              Homepage markup
├── src/
│   ├── css/                Stylesheets
│   ├── fonts/              Self-hosted webfonts
│   ├── img/                Images and project artwork
│   └── js/                 Minimal browser JavaScript
├── scripts/                Build support scripts
├── server/                 PHP contact endpoint and server setup
├── .forgejo/workflows/     Build and deployment automation
└── vite.config.js          Vite build configuration
```


## Working on the site

### Requirements

- Node.js 22
- npm

PHP is not required for local frontend development. The contact endpoint runs separately on `api.gioia.dev`.

### Development

Install dependencies:

```bash
npm install
```

Start the Vite development server:

```bash
npm run dev
```

Vite will print the local URL, normally `http://localhost:5173/`.

### Production build

Build the site into `dist/`:

```bash
npm run build
```

Preview the production build locally:

```bash
npm run preview
```

The primary website is hosted by Render. Static assets retain their source organization when built, including `css/`, `fonts/`, `img/`, and `js/` directories.

### Contact endpoint

The contact form posts to `https://api.gioia.dev/prismatic/contact`. A small PHP endpoint validates submissions and sends them through the gioia.email SMTP server using `msmtp`.

The endpoint is deployed separately from the Render website. See [server/README.md](server/README.md) for its infrastructure and configuration instructions. SMTP credentials live only on the server and must never be committed to this repository.


## Code guidelines

Project conventions and implementation principles are documented in [AGENTS.md](AGENTS.md).