// JavaScript code
document.getElementById("publication-form").addEventListener("submit", function(event) {
    event.preventDefault(); // Prevent the form from submitting in the traditional way

    const categoryFilter = document.getElementById("category_filter").value;
    var data = {
		'action': 'my_action',
        'category':categoryFilter
	};
    
	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
	jQuery.post(ajax_object.ajax_url, data, function(response) {
		updatePageContent(response);
	});
    



});

function updatePageContent(data) {
    // Update the page content with the filtered results
    // For example, if you want to replace the entire content of a div with id "content"
    const contentDiv = document.getElementById("publication-section");
    contentDiv.innerHTML = data; // Assuming the server returns HTML content
}
