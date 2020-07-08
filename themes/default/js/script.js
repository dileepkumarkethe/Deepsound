

// window.onkeydown = function(e) {
//       return !(e.keyCode == 32);
// };


// Show Songs Queue
(function($) {
  $.fn.uncheckableRadio = function() {
    var $root = this;
    $root.each(function() {
      var $radio = $(this);
      if ($radio.prop('checked')) {
        $radio.data('checked', true);
      } else {
        $radio.data('checked', false);
      }
        
      $radio.click(function() {
        var $this = $(this);
        if ($this.data('checked')) {
          $this.prop('checked', false);
          $this.data('checked', false);
          $this.trigger('change');
        } else {
          $this.data('checked', true);
          $this.closest('form').find('[name="' + $this.prop('name') + '"]').not($this).data('checked', false);
        }
      });
    });
    return $root;
  };
}(jQuery));

function decodeHtml(html) {
    var txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}

function Ma_ShowQueue() {
	$('body').toggleClass('data-queue-open');
};

function readURL(input, element) {

  if (input.files && input.files[0]) {
    var reader = new FileReader();

    reader.onload = function(e) {
      $('#' + element).html("<img src='" + e.target.result + "'>");
    }

    reader.readAsDataURL(input.files[0]);
  }
}


function openInNewTab(url) {
  var win = window.open(url, '_blank');
  win.focus();
}

$(window).resize(function(event) {
	$('#light-wave img').width($('#dark-wave').width() + 'px');
});


$(document).on('click', '.amplitude-song-slider', function(event) {
	$("#light-wave").stop();
	if (detectIE()) {
		updateWaveGeneral();
	}
	
});

$(document).on('click', '.amplitude-playback-speed', function(event) {
    updateWaveForm();
});

$(document).on('click', '.amplitude-next, .amplitude-prev', function(event) {
    $('.waveform-small').html("");
    $('.ssong_comments.small-waves').addClass('hidden');
    $("#light-wave").width('0px');
    $("#light-wave").stop();
});

$(document).on('click', '.images', function(event) {

	$("#light-wave").stop(true);
	var offset = this.getClientRects()[0];
    var mouseClickPosition = event.clientX - offset.left;
    if ($('body').attr('data-lang') == 'rtl') {
    	mouseClickPosition = Number(offset.width - mouseClickPosition);
    }
    $('#light-wave').width(mouseClickPosition + 'px');
    Amplitude.setSongPlayedPercentage((mouseClickPosition / $('#dark-waves').width()) * 100);
    if (!$('body').hasClass('audio_playing')) {
    	Amplitude.play();
    	$('body').addClass('player_running');
    }
    if (detectIE()) {
    	updateWaveForm();
    }
});

function detectIE() {
    var ua = window.navigator.userAgent;

    var msie = ua.indexOf('MSIE ');
    if (msie > 0) {
        // IE 10 or older => return version number
        return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
    }

    var trident = ua.indexOf('Trident/');
    if (trident > 0) {
        // IE 11 => return version number
        var rv = ua.indexOf('rv:');
        return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
    }

    var edge = ua.indexOf('Edge/');
    if (edge > 0) {
       // Edge (IE 12+) => return version number
       return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10);
    }

    // other browser
    return false;
}
function updateWaveForm() {
	if (!$('body').hasClass('audio_playing')) {
		return false;
	}
	var getDarkWave = $('#dark-waves').width();
    var getLightWidth = $('#light-wave').width();
    var getWavePercetnage = (getLightWidth / getDarkWave) * 100;
    var duration = $('body').attr('song-duration');
    if (!duration) {
    	return false;
    }
    if ($('.amplitude-playback-speed').hasClass('amplitude-playback-speed-20')) {
    	duration = duration / 2;
    }
    if ($('.amplitude-playback-speed').hasClass('amplitude-playback-speed-15')) {
    	duration = duration / 1.5;
    }
    $("#light-wave").stop(true);
    var Startnumber = (((duration / 100) * (100 - getWavePercetnage)) * 1000);
    $('#light-wave').animate({width: '100%'}, Startnumber, 'linear');
}


jQuery(document).ready(function($) {
    $('#light-wave img').width($('#dark-wave').width() + 'px');
});

function showPurchaseBox() {
	if( window.artist_sell !== 'off' ) {
        if (Amplitude.getActiveSongMetadata().showDemo == 'true') {
            if (($('.amplitude-song-played-progress').val() * 100) > 90) {
                $('#purchase-song').find('.price').text(Amplitude.getActiveSongMetadata().price);
                $('#purchase-song').find('.btn-mat').attr('onclick', 'purchaseTrack("' + Amplitude.getActiveSongMetadata().id + '", $(this));')
                $('#purchase-song').modal('show');
                Amplitude.pause();
                Amplitude.audio().currentTime = 0;
                setTimeout(function () {
                    $("#light-wave").stop(true);
                    $('#light-wave').width('0px');
                }, 200);
            }
        } else if (Amplitude.getActiveSongMetadata().purchase == 'true') {
            if (($('.amplitude-song-played-progress').val() * 100) > 20) {
                $('#purchase-song').find('.price').text(Amplitude.getActiveSongMetadata().price);
                $('#purchase-song').find('.btn-mat').attr('onclick', 'purchaseTrack("' + Amplitude.getActiveSongMetadata().id + '", $(this));')
                $('#purchase-song').modal('show');
                Amplitude.pause();
                Amplitude.audio().currentTime = 0;
                setTimeout(function () {
                    $("#light-wave").stop(true);
                    $('#light-wave').width('0px');
                }, 200);
            }
        }
    }
}
// Home Top Slider
$(document).ready(function(){
	Amplitude.audio().onpause = function () {
		$('.playlist-list-song').removeClass('playing');
		$("#light-wave").stop(true);
	}
	Amplitude.audio().onstop = function () {
		$('.playlist-list-song').removeClass('playing');
		$("#light-wave").stop(true);
	}
	Amplitude.audio().onplaying = function() {
		$('.playlist-list-song').removeClass('playing');
		$('.playlist-list-song[data-id=' + Amplitude.getActiveSongMetadata().u_id + ']').addClass('playing');
		updateWaveGeneral();
		showPurchaseBox();
		
    };
	
});

function updateWaveGeneral() {
	if ($('#current-track').val()) {
		if ($('#current-track').val() != Amplitude.getActiveSongMetadata().id) {
			goToAjaxLink('track/' + Amplitude.getActiveSongMetadata().id);
		}
	}
	if ($('#waveform').attr('data-id') != Amplitude.getActiveSongMetadata().id) {
		$('.waveform-small').html("");
		$('.ssong_comments.small-waves').addClass('hidden');
		$('.player-song-url').attr('href', Amplitude.getActiveSongMetadata().href);
		$('.player-song-url').attr('data-load', Amplitude.getActiveSongMetadata().data_load)
	}
	if (Amplitude.getActiveSongMetadata().purchase == 'true') {
		setPlayerInterval = setInterval(function () {
			showPurchaseBox();
		}, 300);
	}
	//&& $('#current-track').val() == Amplitude.getActiveSongMetadata().id
	if ($('#dark-waves').length > 0 ) {
		var a = Amplitude.audio();
	    var t = a.currentTime;
	    var duration = (a.duration) ? a.duration : $('body').attr('song-duration');
	    var getCurrentPlaying = (Number(t) / $('body').attr('song-duration')) * 100;
	    if (!getCurrentPlaying) {
	    	var getCurrentPlaying = Number($('.amplitude-song-played-progress').attr('value')) * 100;
	    }
	    $('#light-wave').width(getCurrentPlaying + '%');
    	var getDarkWave = document.getElementById('dark-waves').offsetWidth;
	    var getLightWidth = document.getElementById('light-wave').offsetWidth;
	    var getWavePercetnage = (getLightWidth / getDarkWave) * 100;
	    var duration = $('body').attr('song-duration');
	    if (!duration) {
	    	return false;
	    }
	    if ($('.amplitude-playback-speed').hasClass('amplitude-playback-speed-20')) {
	    	duration = duration / 2;
	    }
	    if ($('.amplitude-playback-speed').hasClass('amplitude-playback-speed-15')) {
	    	duration = duration / 1.5;
	    }

        $("#light-wave").stop(true);
	    var Startnumber = (((duration / 100) * (100 - getWavePercetnage)) * 1000);
	    setTimeout(function () {
	    	$('#light-wave').animate({width: '100%'}, Startnumber, 'linear');
	    }, 100);
	}
}
// Recently Played Slider
$(document).ready(function(){
	
});

// New Music Slider
$(document).ready(function(){
	if( typeof $(".nm_slider").owlCarousel !== "undefined" ) {
        $(".nm_slider").owlCarousel({
            margin: 20,
            nav: true,
            dots: false,
            navContainer: '.new_music_btn',
            slideBy: 2,
            items: 7,
            responsive: {
                0: {
                    items: 1
                },
                260: {
                    items: 2
                },
                380: {
                    items: 3
                },
                768: {
                    items: 5
                },
                992: {
                    items: 6
                },
                1300: {
                    items: 7
                }
            }
        });
    }
});


// Trending Header Search Menu
function Ma_OpenTrending() {
  $('.search_dropdown').removeClass('hidden');
};

jQuery(document).click(function(event){
    if (!(jQuery(event.target).closest(".head_search_cont input").length)) {
        jQuery('.search_dropdown').addClass('hidden');
    }
});

// Show Left Side menu
$('#open_slide').on('click', function(event) {
	event.preventDefault();
	$('body').addClass('side_open');
});

// Mobi search bar
$('#open_search').on('click', function(event) {
	event.preventDefault();
	$('body').addClass('search_open');
});
$('#close_search').on('click', function(event) {
	event.preventDefault();
	$('body').removeClass('search_open');
});

function getHashID() {
	return $('.main_session').val();
}

function deletePost() {
	var id = $('#delete-post').attr('data-id');
	if (!id || id == 0) {
		return false;
	}
	$('.feed_post[data-a-id="' + id + '"]').slideUp(200, function () {
		$(this).remove();
	});
	$('#delete-post').modal('hide');
	if ($('#page').attr('data-page') == 'track') {
		location.href = siteUrl();
	}
	$.get(ajaxUrl() + '/delete-post', {id: id});
}



function deleteAlbum(type) {
	var id = $('#delete-album').attr('data-id');
	if (!id || id == 0) {
		return false;
	}
	$('#delete-album').modal('hide');
	setTimeout(function () {
		goToAjaxLink('feed');
	}, 1000);
	$.get(ajaxUrl() + '/delete-album', {id: id, type: type});
}

function deletePlaylist() {
	var id = $('#delete-playlist').attr('data-id');
	if (!id || id == 0) {
		return false;
	}
	$('.playlist-list[data-id="' + id + '"]').slideUp(200, function () {
		$(this).remove();
	});
	$('#delete-playlist').modal('hide');
	$.get(ajaxUrl() + '/playlist/delete-playlist', {id: id});
}

function deleteSong() {
	var id = $('#delete-song').attr('data-id');
	if (!id || id == 0) {
		return false;
	}
	$('.feed_post[data-id="' + id + '"]').slideUp(200, function () {
		$(this).remove();
	});
	$('#delete-song').modal('hide');
	if ($('#page').attr('data-page') == 'track') {
		location.href = siteUrl();
	}
	$.get(ajaxUrl() + '/delete-song', {id: id});
}

function rePost(id, element) {
	if (!id || id == 0) {
		return false;
	}
	element.attr('disabled', 'true');
	element.css({
		cursor: 'progress',
	});
	$.get(ajaxUrl() + '/re-post', {id: id}, function (data) {
		element.removeAttr('disabled');
		element.css({
			cursor: 'pointer',
		});
		if (data.status == 200) {
			$('#reposted').modal('show');
			setTimeout(function () {
				$('#reposted').modal('hide');
			}, 3000);
		}
	});
}

$(document).on("click", ".select-playlist-list", function () {
	$(this).toggleClass('active');
});

function closeModal() {
	$('.modal-backdrop').remove();
	$('body').removeClass('modal-open');
	$('body').css('padding-right', '0');
}
function getPlayLists(id) {
	if (!id) {
		return false;
	}
	$('body').css({
		cursor: 'progress',
	});
	$('#playlists').remove();
	$.get(ajaxUrl() + "/playlist/get-playlists", {id: id}, function (data) {
		if (data.status == 200) {
			$("body").append(data.html);
			setTimeout(function () {
				$('#playlists').modal('show');
			}, 200)
		} else if (data.status == 300) {
			$('#login_box').modal('show');
		}
		$('body').css({
			cursor: 'default',
		});
	});
}

function getEditForm(id) {
	if (!id) {
		return false;
	}
	$('body').css({
		cursor: 'progress',
	});
	$.get(ajaxUrl() + "/playlist/get-edit-form", {id: id}, function (data) {
		if (data.status == 200) {
			$("#edit-playlist").html(data.html);
			setTimeout(function () {
				$('#edit-playlist-form').modal('show');
			}, 200)
		}
		$('body').css({
			cursor: 'default',
		});
	});
}

function playPlayListSongs(id, type) {
	if (!id || id == 0) {
		return false;
	}
	url = 'playlist/get-playlist-songs';
	if (type) {
		if (type == 'album') {
			url = 'get-album-songs';
		}
	}
	$('#bar_loading').show().animate({width:20 + 80 * Math.random() + "%"}, 200);
    var dataSongs = null;
	$.get(ajaxUrl() + '/' + url, {id: id}, function (data) {
		newsong = false;
		if (data.status == 200) {
			if (!$('body').attr('first-play')) {
				Amplitude.removeSong(0);
				$('body').attr('first-play', "true");
			}
			dataSongs = data.songs;
			dataSongs.forEach(function(item) {

			    $.get(ajaxUrl() + '/get-song-info', {id: item}, function (data) {
					if (data.status == 200 && dataSongs.length > 0) {
						var songObject = {
					        "name": data.songTitle,
					        "artist": data.artistName,
					        "album": data.albumName,
					        "url": data.songURL,
					        "cover_art_url": data.coverURL,
					        "id": item,
					        "u_id": data.songID,
					        "data_load": 'track/' + item,
					        "href": data.songPageURL,
					        "duration": data.songDuration,
					        "purchase": data.purchase,
					        'price': data.price,
					        "is_favoriated": data.is_favoriated
						};

						var songIndex = Amplitude.addSong(songObject);
						data.qID = songIndex;
						songObject.qID = songIndex;
						if ($('#queue-' + item).length == 0) {
							addToQueue(data, songObject, false);
						}
						//addView(data.songID);
						if (newsong == false) {
							$('.amplitude-song-container').removeClass('amplitude-playing');
							$('.amplitude-song-container[amplitude-song-index="' + songIndex + '"]').addClass('amplitude-playing');
						}
						newsong = true;
					}
					$('.ma_player').removeClass('closed');
					$('body').addClass('player_running');

				});
			});
			$('.amplitude-repeat').trigger('click');
		}
		$('#bar_loading').animate({width:"100%"}, 200).fadeOut(300, function() {
           $(this).width("0");
        });

	});

	setTimeout(function () {
        if( dataSongs !== null ) {
            for (i = 1; i < dataSongs.length; i++) {
                $('.amplitude-prev').trigger('click');
            }
        }
    },300);

}

function getplaylistSong(id) {

}

function getPlayListShareForm(id, element) {
	if (!id || id == 0) {
		return false;
	}
	$('body').css({
		cursor: 'progress',
	});
	$('#share_playlist_modal').remove();
	$.get(ajaxUrl() + '/playlist/get-share-modal', {id: id}, function (data) {
		if (data.status == 200) {
			$('body').append(data.html);
			$('#share_playlist_modal').modal('show');
			$('body').css({
				cursor: 'default',
			});
		}
	});
}


function getShareModal(id, element) {
	if (!id || id == 0) {
		return false;
	}
	element.attr('disabled', 'true');
	element.css({
		cursor: 'progress',
	});
	$('#share_music_modal').remove();
	$.get(ajaxUrl() + '/get-share-modal', {id: id}, function (data) {
		if (data.status == 200) {
			$('body').append(data.html);
			element.removeAttr('disabled');
			$('#share_music_modal').modal('show');
			element.css({
				cursor: 'pointer',
			});
		}
	});
}

function addView(id) {
	if (!id || id == 0) {
		return false;
	}
	setTimeout(function () {
        Fingerprint2.get(function (components) {
          $.post(ajaxUrl() + '/add-view', {components: components, id: id});
        })  
    }, 500);
}

function validate_fileupload(fileName, type)
{
    var allowed_extensions = type;
    var file_extension = fileName.split('.').pop().toLowerCase(); // split function will split the filename by dot(.), and pop function will pop the last element from the array which will give you the extension as well. If there will be no extension then it will return the filename.

    for(var i = 0; i <= allowed_extensions.length; i++)
    {
        if(allowed_extensions[i]==file_extension)
        {
            return true; // valid file extension
        }
    }

    return false;
}

function clearQueues() {
	$.get(ajaxUrl() + '/add-queue', {reset: true}, function(data) {
		location.reload();
	});
}

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

$(document).on('click', '.sections li', function () {
	$('.sections li').removeClass('active');
	$(this).addClass('active');
});

function goToAjaxLink(ajax_link) {
	$('#container_content').append('<a href="#" id="redirect-user" data-load="' + ajax_link + '"></a>');
	$('#redirect-user').trigger('click');
}

function makeid() {
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for (var i = 0; i < 10; i++)
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}
function nl2br (str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
}

function escapeHTML(string) {
    var pre = document.createElement('pre');
    var text = document.createTextNode( string );
    pre.appendChild(text);
    return pre.innerHTML;
}


/*!
 * @preserve
 *
 * Readmore.js jQuery plugin
 * Author: @jed_foster
 * Project home: http://jedfoster.github.io/Readmore.js
 * Licensed under the MIT license
 *
 * Debounce function from http://davidwalsh.name/javascript-debounce-function
 */
!function(t){"function"==typeof define&&define.amd?define(["jquery"],t):"object"==typeof exports?module.exports=t(require("jquery")):t(jQuery)}(function(t){"use strict";function e(t,e,i){var o;return function(){var n=this,a=arguments,s=function(){o=null,i||t.apply(n,a)},r=i&&!o;clearTimeout(o),o=setTimeout(s,e),r&&t.apply(n,a)}}function i(t){var e=++h;return String(null==t?"rmjs-":t)+e}function o(t){var e=t.clone().css({height:"auto",width:t.width(),maxHeight:"none",overflow:"hidden"}).insertAfter(t),i=e.outerHeight(),o=parseInt(e.css({maxHeight:""}).css("max-height").replace(/[^-\d\.]/g,""),10),n=t.data("defaultHeight");e.remove();var a=o||t.data("collapsedHeight")||n;t.data({expandedHeight:i,maxHeight:o,collapsedHeight:a}).css({maxHeight:"none"})}function n(t){if(!d[t.selector]){var e=" ";t.embedCSS&&""!==t.blockCSS&&(e+=t.selector+" + [data-readmore-toggle], "+t.selector+"[data-readmore]{"+t.blockCSS+"}"),e+=t.selector+"[data-readmore]{transition: height "+t.speed+"ms;overflow: hidden;}",function(t,e){var i=t.createElement("style");i.type="text/css",i.styleSheet?i.styleSheet.cssText=e:i.appendChild(t.createTextNode(e)),t.getElementsByTagName("head")[0].appendChild(i)}(document,e),d[t.selector]=!0}}function a(e,i){this.element=e,this.options=t.extend({},r,i),n(this.options),this._defaults=r,this._name=s,this.init(),window.addEventListener?(window.addEventListener("load",c),window.addEventListener("resize",c)):(window.attachEvent("load",c),window.attachEvent("resize",c))}var s="readmore",r={speed:100,collapsedHeight:200,heightMargin:16,moreLink:'<a href="#">Read More</a>',lessLink:'<a href="#">Close</a>',embedCSS:!0,blockCSS:"display: block; width: 100%;",startOpen:!1,blockProcessed:function(){},beforeToggle:function(){},afterToggle:function(){}},d={},h=0,c=e(function(){t("[data-readmore]").each(function(){var e=t(this),i="true"===e.attr("aria-expanded");o(e),e.css({height:e.data(i?"expandedHeight":"collapsedHeight")})})},100);a.prototype={init:function(){var e=t(this.element);e.data({defaultHeight:this.options.collapsedHeight,heightMargin:this.options.heightMargin}),o(e);var n=e.data("collapsedHeight"),a=e.data("heightMargin");if(e.outerHeight(!0)<=n+a)return this.options.blockProcessed&&"function"==typeof this.options.blockProcessed&&this.options.blockProcessed(e,!1),!0;var s=e.attr("id")||i(),r=this.options.startOpen?this.options.lessLink:this.options.moreLink;e.attr({"data-readmore":"","aria-expanded":this.options.startOpen,id:s}),e.after(t(r).on("click",function(t){return function(i){t.toggle(this,e[0],i)}}(this)).attr({"data-readmore-toggle":s,"aria-controls":s})),this.options.startOpen||e.css({height:n}),this.options.blockProcessed&&"function"==typeof this.options.blockProcessed&&this.options.blockProcessed(e,!0)},toggle:function(e,i,o){o&&o.preventDefault(),e||(e=t('[aria-controls="'+this.element.id+'"]')[0]),i||(i=this.element);var n=t(i),a="",s="",r=!1,d=n.data("collapsedHeight");n.height()<=d?(a=n.data("expandedHeight")+"px",s="lessLink",r=!0):(a=d,s="moreLink"),this.options.beforeToggle&&"function"==typeof this.options.beforeToggle&&this.options.beforeToggle(e,n,!r),n.css({height:a}),n.on("transitionend",function(i){return function(){i.options.afterToggle&&"function"==typeof i.options.afterToggle&&i.options.afterToggle(e,n,r),t(this).attr({"aria-expanded":r}).off("transitionend")}}(this)),t(e).replaceWith(t(this.options[s]).on("click",function(t){return function(e){t.toggle(this,i,e)}}(this)).attr({"data-readmore-toggle":n.attr("id"),"aria-controls":n.attr("id")}))},destroy:function(){t(this.element).each(function(){var e=t(this);e.attr({"data-readmore":null,"aria-expanded":null}).css({maxHeight:"",height:""}).next("[data-readmore-toggle]").remove(),e.removeData()})}},t.fn.readmore=function(e){var i=arguments,o=this.selector;return e=e||{},"object"==typeof e?this.each(function(){if(t.data(this,"plugin_"+s)){var i=t.data(this,"plugin_"+s);i.destroy.apply(i)}e.selector=o,t.data(this,"plugin_"+s,new a(this,e))}):"string"==typeof e&&"_"!==e[0]&&"init"!==e?this.each(function(){var o=t.data(this,"plugin_"+s);o instanceof a&&"function"==typeof o[e]&&o[e].apply(o,Array.prototype.slice.call(i,1))}):void 0}});


