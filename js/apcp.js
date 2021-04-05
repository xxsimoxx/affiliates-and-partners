function sortChildren(dataSelector) {

	const container = document.querySelector('#apcp-container');

	Array.from(container.children)
		.sort((a, b) => ('' + a.dataset[dataSelector]).localeCompare(b.dataset[dataSelector]))
		.forEach(element => container.appendChild(element));

	return false;
}


function showOnly() {

	let text = document.getElementById("apcp-search-field").value;
	let divs = document.getElementsByClassName('apcp-element');

	for (let x = 0; x < divs.length; x++) {

		let div = divs[x];
		let content = div.innerHTML.trim();

		if (!content.includes(text)) {
			div.style.display = 'none';
		} else {
			div.style.display = '';
		}

	}
}