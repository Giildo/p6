$(function(){
    var collectionHolderPic;
    var collectionHolderVid;

    // Get the ul that holds the collection of tags
    collectionHolderPic = $('#trick_pictures');
    collectionHolderVid = $('#trick_videos');

    // add the "add a tag" anchor and li to the tags ul
    collectionHolderPic.append('<a href="#" class="add_picture btn btn-success">Ajouter une image</a>');
    collectionHolderVid.append('<a href="#" class="add_video btn btn-success">Ajouter une vidéo</a>');

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    collectionHolderPic.data('index', collectionHolderPic.find(':input').length);
    collectionHolderVid.data('index', collectionHolderPic.find(':input').length);

    $('.add_picture').on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // add a new tag form (see next code block)
        addTagForm(collectionHolderPic);
    });


    $('.add_video').on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // add a new tag form (see next code block)
        addTagForm(collectionHolderVid);
    });

    function addTagForm(collectionHolderPic) {
        // Get the data-prototype explained earlier
        var prototype = collectionHolderPic.data('prototype');

        // get the new index
        var index = collectionHolderPic.data('index');

        var newForm = prototype;
        // You need this only if you didn't set 'label' => false in your tags field in TaskType
        // Replace '__name__label__' in the prototype's HTML to
        // instead be a number based on how many items we have
        // newForm = newForm.replace(/__name__label__/g, index);

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        newForm = newForm.replace(/__name__/g, index);

        // increase the index with one for the next item
        collectionHolderPic.data('index', index + 1);

        // Display the form in the page in an li, before the "Add a tag" link li
        collectionHolderPic.append(newForm);
    }


    function addTagForm(collectionHolderVid) {
        // Get the data-prototype explained earlier
        var prototype = collectionHolderVid.data('prototype');

        // get the new index
        var index = collectionHolderVid.data('index');

        var newForm = prototype;
        // You need this only if you didn't set 'label' => false in your tags field in TaskType
        // Replace '__name__label__' in the prototype's HTML to
        // instead be a number based on how many items we have
        // newForm = newForm.replace(/__name__label__/g, index);

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        newForm = newForm.replace(/__name__/g, index);

        // increase the index with one for the next item
        collectionHolderVid.data('index', index + 1);

        // Display the form in the page in an li, before the "Add a tag" link li
        collectionHolderVid.append(newForm);
    }
});