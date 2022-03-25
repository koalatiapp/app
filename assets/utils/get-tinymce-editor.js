import { html } from "lit";
import "@tinymce/tinymce-webcomponent";

window.tinymce_config = {
	automatic_uploads: true,
};

window.tinymce_init_callback = (e) => {
	e.target.targetElm.getRootNode().host.classList.add("ready");
};

export default function getTinyMceEditor(editorId, content = "") {
	return html`
		<tinymce-editor
			api-key="${env.TINYMCE_API_KEY}"
			src="/ext/tinymce/tinymce.min.js"
			skin="oxide-koalati"
			content_css="default-koalati"
			plugins="advlist autolink link image media table lists codesample quickbars"
			menubar="false"
			toolbar="bold italic underline | bullist numlist | link quickimage codesample"
			height="12em"
			images_upload_url="${Routing.generate("api_tinymce_upload_image")}"
			images_upload_credentials="true"
			on-Init="tinymce_init_callback"
			id="tinymce-editor-${editorId}">${content}</tinymce-editor>
	`;
}
