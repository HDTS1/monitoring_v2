(function () {
    
    let camera = function(el){
        
        let toggleFullScreen = function() {
                const video = this;

                if (video.requestFullscreen) {
                  video.requestFullscreen();
                } else if (video.mozRequestFullScreen) { // Firefox
                  video.mozRequestFullScreen();
                } else if (video.webkitRequestFullscreen) { // Chrome, Safari and Opera
                  video.webkitRequestFullscreen();
                } else if (video.msRequestFullscreen) { // IE/Edge
                  video.msRequestFullscreen();
                }
        };
        
        
        let selectCamera = $("data#setCamera[data]").attr("data");
        selectCamera = $.parseJSON(selectCamera);

        
        zapis("/rest/service/camera", {data: selectCamera, json: true}, function (odpoved) {
            
            console.log(odpoved);

            if (!odpoved.data.status) {
                setTimeout(function(){
                    camera(el);
                }, 3000); 
                return true;
            }


            const url = odpoved.data.status.url;
            const match = url.match(/([^\/]+)$/);
            let source = "https://streaming.48toj6g9v0y978cdn.com:5443/live/streams/" + match[0] + ".m3u8";


            var player = videojs('video');
            player.src({
                src: source,
                type: 'application/x-mpegURL' 
            });


            player.on('error', function() {
                let error = player.error();
                if (error) {
                    console.log('Chyba kód:', error.code);
                    console.log('Chyba správa:', error.message);
                }
            });

            player.on('loadeddata', function() {
                let hls = player.hls;
                if (hls && !hls.media) {
                    console.log('Nie sú dostupné ďalšie .ts segmenty.');
                }
            });


            player.on('hlsPlaylist', function(event, data) {
                console.log('Nová playlist udalosť:', data);
                // Skontrolujte stav playlistu, či sú ešte segmenty k dispozícii
            });

            player.ready(function() {
                player.load();
                var playPromise = player.play();
                if (playPromise !== undefined && typeof playPromise.then === 'function') {
                    playPromise.then(function() {
                        console.log("Playback started successfully");
                        $(el).remove();
                    }).catch(function(error) {
                        console.log("Playback failed, removing overlay:", error);
                        $(el).remove();
                    });
                } else {
                    console.log("Playback started (no Promise returned)");
                    $(el).remove();
                }
            });
            

            
            

        });
        
    };
    
    $("div.play_camera").click(function(){
        $("div#clickPlay").remove();
        $("div#clickWait").show();
        
        camera(this);
    });
    



})();


