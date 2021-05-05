import { ZeroMd } from "zero-md";

export default class NbMarkdown extends ZeroMd {

	constructor()
	{
		super();
		this.config.cssUrls = [
			"https://cdn.jsdelivr.net/gh/PrismJS/prism@1/themes/prism-okaidia.css",
		];
		this.config.hostCss = `
			${this.config.hostCss}
			a { color: var(--color-blue); text-decoration: none; }
			a:hover { text-decoration: underline; }
			p { line-height: 1.4; }
			:is(ol, ul) { line-height: 1.4; }
			li + li { margin-top: .5em; }
			code { padding: .2em .4em; margin: 0; font-family: SFMono-Regular,Consolas,Liberation Mono,Menlo,monospace; font-size: .85em; background-color: rgba(27,31,35,.05); border-radius: 3px; }
			code[class*="language-"] { padding: 0; }
			.markdown-body :is(code, pre)[class*="language-"] { font-size: 13px; }

			@media (prefers-color-scheme: dark) {
				code { background-color: rgb(146 170 255 / 10%); }
			}
		`;

		if (this.hasAttribute("barebones")) {
			this.config.hostCss = `
				${this.config.hostCss}
				:host { display: inline; }
				div { display: inline; }
				p { display: inline; margin: 0; }
			`;
		}
	}

	connectedCallback()
	{
		const script = this.querySelector("script[type='text/markdown']");
		if (script) {
			script.setAttribute("data-dedent", 1);
		}
		super.connectedCallback();
		this._makeLinksOpenInNewTab();
	}

	_makeLinksOpenInNewTab()
	{
		this.addEventListener("zero-md-rendered", () => {
			for (const link of this.shadowRoot.querySelectorAll("a")) {
				if (link.host != window.location.host) {
					link.setAttribute("target", "_blank");
					link.setAttribute("rel", "noopener");
				}
			}
		});
	}
}

customElements.define("nb-markdown", NbMarkdown);
