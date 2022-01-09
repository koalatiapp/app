import { css } from "lit";

export default css`
	:host { box-sizing: border-box; -webkit-font-smoothing: inherit; }

	* { font-family: inherit; font-size: inherit; box-sizing: inherit; -webkit-font-smoothing: inherit; }
	a { text-decoration: underline; }
	p { line-height: 1.4; }
	h1,
	h2,
	h3,
	h4,
	h5,
	h6 { margin: 0 auto; }
	h1 { font-size: 1.75rem; font-weight: 700; line-height: 1.5; }
	h2 { margin-bottom: 1.5em; font-size: 1.2rem; font-weight: 600; line-height: 1.25; }
	h3 { font-size: 1.2rem; font-weight: 500; line-height: 1.25; }
	strong { font-weight: 600; }
	hr,
	.spacer { width: 100%; height: 1px; margin: 30px auto; background-color: #F4F4F4; border: none; }
	.spacer { height: 0; }
	.spacer.small { margin: .8rem auto; }

	*:where(:focus-visible) { position: relative; outline: none; }
	*:focus-visible::before { content: ' '; position: absolute; top: -7px; left: -7px; width: 100%; height: 100%; padding: 4px; border: 3px solid var(--color-blue-80); border-radius: .5rem; box-shadow: 0 0 1rem 0 rgba(var(--shadow-rgb), .2); }

	@media (prefers-color-scheme: dark) {
		*:focus-visible::before { box-shadow: 0 0 1rem 0 rgba(var(--shadow-rgb), .5); }
	}
`;
