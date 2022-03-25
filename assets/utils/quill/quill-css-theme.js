import { css } from "lit";

export default css`
	.ql-toolbar.ql-snow { margin-top: 6px; font-family: inherit; background-color: var(--color-gray-light); border: 2px solid var(--color-gray-light); border-top-left-radius: 8px; border-top-right-radius: 8px; }
	.ql-container { margin-bottom: 6px; font-family: inherit; }
	.ql-container.ql-snow { margin-bottom: 6p; font-size: 0.95rem; color: var(--color-gray-darker); background-color: var(--color-white); border: 2px solid var(--color-gray-light); border-radius: 0; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; outline: none; box-shadow: 0 2px 10px 0 rgba(var(--shadow-rgb), 0.025); box-sizing: border-box; -webkit-font-smoothing: antialiased; transition: border-color 0.25s ease 0s, box-shadow 0.25s ease 0s; }
	.ql-editor { min-height: 5em; }

	.ql-snow.ql-toolbar button.ql-code-block,
	.ql-snow .ql-toolbar button.ql-code-block { background-color: var(--color-blue-darker); border-radius: .25em; }
	.ql-snow.ql-toolbar button.ql-code-block .ql-stroke,
	.ql-snow .ql-toolbar button.ql-code-block .ql-stroke { stroke: var(--color-white); }

	.ql-snow .ql-picker,
	.ql-snow .ql-tooltip { color: var(--color-blue-dark); }
	.ql-snow .ql-stroke,
	.ql-snow .ql-stroke-miter { stroke: var(--color-blue-dark);}
	.ql-snow .ql-fill,
	.ql-snow .ql-stroke.ql-fill { fill: var(--color-blue-dark); }

	.ql-snow .ql-editor code { padding: .2em .4em; margin: 0; font-family: SFMono-Regular,Consolas,Liberation Mono,Menlo,monospace; font-size: .85em; background-color: rgba(27,31,35,.05); border-radius: 3px; }

	@media (prefers-color-scheme: dark) {
		.ql-snow .ql-picker-options,
		.ql-snow .ql-color-picker.ql-background .ql-picker-item,
		.ql-snow .ql-tooltip { background-color: var(--color-white); }

		.ql-snow .ql-picker,
		.ql-snow .ql-tooltip { color: var(--color-gray-darker); }
		.ql-snow .ql-stroke,
		.ql-snow .ql-stroke-miter { stroke: var(--color-gray-darker);}
		.ql-snow .ql-fill,
		.ql-snow .ql-stroke.ql-fill { fill: var(--color-gray-darker); }

		@media (pointer: coarse) {
			.ql-snow.ql-toolbar button:hover:not(.ql-active),
			.ql-snow .ql-toolbar button:hover:not(.ql-active) {
			  	color: var(--color-gray-darker);
			}
			.ql-snow.ql-toolbar button:hover:not(.ql-active) .ql-fill,
			.ql-snow .ql-toolbar button:hover:not(.ql-active) .ql-fill,
			.ql-snow.ql-toolbar button:hover:not(.ql-active) .ql-stroke.ql-fill,
			.ql-snow .ql-toolbar button:hover:not(.ql-active) .ql-stroke.ql-fill {
			  	fill: var(--color-gray-darker);
			}
			.ql-snow.ql-toolbar button:hover:not(.ql-active) .ql-stroke,
			.ql-snow .ql-toolbar button:hover:not(.ql-active) .ql-stroke,
			.ql-snow.ql-toolbar button:hover:not(.ql-active) .ql-stroke-miter,
			.ql-snow .ql-toolbar button:hover:not(.ql-active) .ql-stroke-miter {
			  	stroke: var(--color-gray-darker);
			}
		}
	}
`;
