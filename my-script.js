
$(document).ready(function() {

    var page = 2; 
    // Function to handle AJAX request
    function makeAjaxRequest(typeFilter,categoryFilter,dateFilter,load_more) {
        var data = {
            'action': 'my_action',
            'type':typeFilter,
            'category':categoryFilter,
            'order':dateFilter,
            'page':page
        };
          
        if(!load_more) 
          data.page = 1;
        // We can also pass the url value separately from ajaxurl for front end AJAX implementations
        jQuery.post(ajax_object.ajax_url, data, function(response) {
            updatePageContent(response,load_more);
            if(load_more)
              page++;
        });
        
    
    }

    
    function updatePageContent(data,load_more) {
 
    const element = document.getElementById("publication-section");       
    if(load_more)
      $('#card_container').append(data);
    // else
    // $('#card_container').html = data; // Assuming the server returns HTML content
    }


    // Event handlers for select boxes
    $('#select_1, #select_2, #select_3').change(function() {
        const typeFilter = document.getElementById("select_1").value;
        const categoryFilter = document.getElementById("select_2").value;
        const dateFilter = document.getElementById("select_3").value;

        makeAjaxRequest(typeFilter, categoryFilter, dateFilter,false);

        // const contentDiv = document.getElementById("publication-section");
        // contentDiv.innerHTML = 'asdasds'; // Assuming the server returns HTML content

    });

    document.getElementById('load-more-button').addEventListener('click', function () {
        const typeFilter = document.getElementById("select_1").value;
        const categoryFilter = document.getElementById("select_2").value;
        const dateFilter = document.getElementById("select_3").value;
        load_more=true;
        makeAjaxRequest(typeFilter, categoryFilter, dateFilter,load_more);      
    });
});


