$(document).ready(function () {
    //!verboseBuild || console.log('-- starting proton.imageGallery build');

    proton.imageGallery.build();
});

proton.imageGallery = {
    build: function () {
        // Initiate imageGallery events
        proton.imageGallery.events();

        

        //!verboseBuild || console.log('            proton.imageGallery build DONE');

    },
    events: function () {
        //!verboseBuild || console.log('            proton.imageGallery binding events');

        
    },
    makeDropzone: function (template) {
        !verboseBuild || console.log('            proton.imageGallery.makeDropzone()');

        

    }
}