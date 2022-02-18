let mousePos = {
	x: 0,
	y: 0,
};

window.addEventListener("mousemove", e => {
	mousePos.x = e.clientX;
	mousePos.y = e.clientY;
});

export default function getMousePosition() {
	return mousePos;
}
