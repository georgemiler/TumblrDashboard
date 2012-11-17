site = {
	'rootURL'    : './',
	'fn'         : {},
	'lastPostID' : null,
	'timeoutID'  : null,
	'pageNum'	 : 1,
	'itemLimit'  : 20,
	'isLoading'  : 0,
	'slideCount' : 0,
	'photos'     : {},
	'isSlideshow': 0,
};

site.init = function() {
	$(document).on('ready', function(e) {
		console.log('site.init: ready');

		if (site.auth) {
			site.getUser();
		}

		site.page();
	});
};

site.page = function() {

	$(window).scroll(function() {
		if ($(window).scrollTop() + $(window).height() === $(document).height()) {
			$('#load-more').click();
		}
	});

	$('#load-more').on('click', function(e) {
        e.preventDefault();

		var button = $(this);
		var buttonText = button.text();

		if (site.isLoading) { 
			console.log('Already loading a page! Please wait.');
			return;
		}

		button.html('Loading &hellip;');

		var options = { 
			'limit'  : site.itemLimit,
			'offset' : (site.pageNum * site.itemLimit) 
		};

		site.getDashboard(options, {
			onBefore: function() {
				console.log('Loading Dashboard Page: ' + (site.pageNum + 1));
				button.html('Loading &hellip;');
				site.isLoading = 1;
			},
			onSuccess: function(boxes) {
				if (boxes) {
					boxes.appendTo('#dashboard');	
					site.pageNum += 1;
				}			},
			onFailure: function() {
			},
			onAfter: function() {
				site.isLoading = 0;
				button.html(buttonText);
			}
		});
	});

	$('#update').on('click', function(e) {
        e.preventDefault();

		var button = $(this);
		var buttonText = button.html();

		site.getDashboard({ 'since_id': site.lastPostID }, {
			onBefore: function() {
				button.html('Updating &hellip;');
			},
			onSuccess: function(boxes) {
				if (boxes) {
					console.log('Prepending new boxes.');
					boxes.prependTo('#dashboard');		
				} else {
					console.log('No new boxes.');
				}

				site.timeoutID = setTimeout(function() {
					$('#update').click();
				}, 10000);
			},
			onFailure: function() {
			},
			onAfter: function() {
				button.html(buttonText);
			}
		});	
	});

	$('#logout').on('click', function(e) {
        e.preventDefault();
		console.log('site.logout: click');

		$('.auth-true').addClass('hide');
		$('.auth-false').removeClass('hide');
		$('#user-name').html('');
	});

	$('#login').on('click', function(e) {
        e.preventDefault();
		console.log('site.login: click');

		window.name = 'loginWindow';

        w = site.popupWindow('login.php', 'TumblrOauth', 920, 500);

        if (window.focus) {
            w.focus();
        }

        site.onAfterTumblrResponse = function(outcome, data) {
			console.log('site.onAfterTumblrResponse');
			if (outcome === 'success') {
				if (data.meta.msg === 'OK' && data.meta.status === 200) {
					site.onLoginSuccess(data);
				}
			}
			console.log(outcome);
			console.log(data);
        };
	});

	$('#slideshow-stop').on('click', function(e) {
        e.preventDefault();
		console.log('site.slideshow: stop');

		var button = $(this);

		site.isSlideshow = 0;

		$('#slideshow-play').removeClass('hide');
		$('#slideshow-controls').addClass('hide');

		$('#slideshow').cycle('destroy').html('');

		site.shroud(0);
	});

	$('#slideshow-play').on('click', function(e) {
        e.preventDefault();
		console.log('site.slideshow: play');

		var button = $(this);

		button.addClass('hide');
		$('#slideshow-controls').removeClass('hide');

		site.isSlideshow = 1;
		site.slidePosition = 1;

		console.log(site.photos);

		var counter = site.getBoxCount();

		if (counter) {

			site.shroud(1);

			var win = $(window);
			var winHeight = win.height() - 70; // nav offset.

			var slideshow = $('#slideshow');

			var slideWidth = slideshow.width();

			slideshow.css({
				'display' : 'none',
				'height'  : winHeight + 'px'
			});

			for (var id in site.photos) {
				site.slideCount += 1;

				var img = site.photos[id];

				var slide = site.getSlide(img, slideWidth, winHeight);

				slide.appendTo(slideshow);
			}

			slideshow.cycle({ 
				'fx' : 'fade', 
				'timeout' : 4000, 
				'delay' : -2000, 
				//'autostop' : 1, 
				'pause': 1,
				'containerResize': 1,
				'end' : function() {  
					console.log('site.slideshow: cycle.end()');
				},
				'before' : function(curr, next, opts) { 
					console.log('site.slideshow: cycle.before()');

					console.log('site.slidePosition: ' + site.slidePosition + ' of ' + site.slideCount);
					site.slidePosition += 1;

					// on the first pass, addSlide is undefined (plugin hasn't yet created the fn); 
					// when we're finshed adding slides we'll null it out again 
					if ( ! opts.addSlide ) 
					{
						return;
					}

					var boxCount = site.getBoxCount();
					var slideCount = site.slideCount;

					console.log('boxCount: ' + boxCount);
					console.log('slideCount: ' + slideCount);

					if (boxCount > slideCount) {
						console.log('Slideshow: Add New Slides');

						var i = 0
						for (var id in site.photos) {
							i += 1;

							// continue from point at which counter is greater than number of slides.
							if (i <= slideCount) {
								continue;
							}

							site.slideCount += 1;

							console.log('Adding Slide: ' + i);

							var img = site.photos[id];

							var slide = site.getSlide(img, slideWidth, winHeight);

							// add our next slide 
							opts.addSlide(slide);
						} 
					}
				},
				'after': function(curr, next, opts, fwd) {
					console.log('site.slideshow: cycle.after()');

					// reset slide counters.
					if (site.slidePosition > site.slideCount) { 
						site.slidePosition = 1;
					}
				}
			});

			slideshow.show();

			var wrapper = $('<div>').css({
				'position': 'absolute',
				'top': '55px',
				'left': Math.floor((win.width() - slideshow.width()) / 2) + 'px'
			});

			slideshow.wrap(wrapper);
		}
	});
};

site.getUser = function() {
	var request = $.ajax({
		url: 'get-user.php',
		dataType: 'json',
		beforeSend: function(jqXHR, settings) {
			console.log('site.getUser: beforeSend');	
		}
	});

	request.done(function(data) {
		console.log('site.getUser: request.done');
		console.log(data);
		if (data.outcome === 'success') {
			if (data.meta.msg === 'OK' && data.meta.status === 200) {
				site.onLoginSuccess(data);
			}
		} else {
			site.login();
		}
	});

	request.fail(function(jqXHR, textStatus) {
		console.log('site.getUser: request.fail');	
		alert( "Request failed: " + textStatus );
	});
};

site.getDashboard = function(options, callbacks) {
	console.log('site.getDashboard');

	var requestData = options || {};

	console.log(requestData);

	var request = $.ajax({
		type: 'POST',
		url: 'get-dashboard.php',
		data: requestData,
		dataType: 'json',
		beforeSend: function(jqXHR, settings) {
			console.log('site.getDashboard: beforeSend');	

			if (typeof callbacks.onBefore === 'function') {
				callbacks.onBefore(jqXHR, settings);
			}
		}
	});

	request.done(function(data) {
		console.log('site.getDashboard: request.done');
		console.log(data);

		var boxes = null;

		if (typeof data.response !== 'undefined') {
			boxes = site.getBoxes(data.response.posts);			
		}

		if (typeof callbacks.onSuccess === 'function') {
			callbacks.onSuccess(boxes);
		}
	});

	request.fail(function(jqXHR, textStatus) {
		console.log('site.getDashboard: request.fail');	

		if (typeof callbacks.onFailure === 'function') {
			callbacks.onFailure(jqXHR, textStatus);
		}

		alert( "Request failed: " + textStatus );
	});

	request.always(function() {
		console.log('site.getDashboard: request.always');	

		if (typeof callbacks.onAfter === 'function') {
			callbacks.onAfter();
		}
	});
};

site.shroud = function(state) {
	if (state) {
		console.log('site.shroud: on');

		var shroud = $('<div>').attr('id', 'shroud').css({
			'background': '#000',
			'opacity'	: '0.5',
			'height'	: '100%',
			'position'	: 'absolute',
			'top'		: '0',
			'width'		: '100%',
			'z-index'	: '1'
		});

		$('body').append(shroud).css('overflow', 'hidden');

	} else {
		console.log('site.shroud: off');

		$('#shroud').remove();

		$('body').css('overflow', 'auto');

	}
};

site.getSlide = function(img, maxWidth, maxHeight) {
	var div = $('<div>').addClass('slide').css({
		'width'  : maxWidth + 'px'
	});

	img.css({
		'max-height' : maxHeight + 'px',
		'max-width'  : maxWidth + 'px'
	}).appendTo(div);

	return div;
};

site.getBoxes = function(posts) {

	var boxes = null;

	var firstRun = (site.lastPostID === null);

	var placeholder = $('<div>');

	var postsLen = posts.length;

	var photosCount = 0;

	console.log('site.displayPosts: postsLen = ' + postsLen);

	for (var i = 0; i < postsLen; i += 1) {
		var post = posts[i];

		var isNew = (typeof site.photos[post.id] === 'undefined');

		if (post.type === 'photo' && isNew) {
			photosCount += 1;

			var photo = post.photos[0];
			var thumb = photo.alt_sizes.pop();
			var full  = photo.original_size;

			var a = $('<a>').attr({
				'title'  : post.blog_name,
				'href'   : post.post_url,
				'target' : '_blank'
			}).data({
				'post_date' : post.date,
				'post_id'	: post.id,
				'post_url'  : post.post_url,
				'post_time' : post.timestmap,
				'post_key'  : post.reblog_key,
				'post_type'	: post.type
			});

			a.append($('<img>').attr({
				'width' : thumb.width,
				'height': thumb.height,
				'src'   : thumb.url
			}));

			site.photos[post.id] = $('<img>').attr({
				'width' : full.width,
				'height': full.height,
				'src'   : full.url
			});

			//a.append($('<span>').html(post.caption).hide());

			var div = $('<div>').addClass('box');

			a.appendTo(div);

			//div.appendTo(dashboard);

			div.appendTo(placeholder);

			if (post.id > site.lastPostID) {
				site.lastPostID = post.id;	
			}
		}

		//console.log('post: ' + post.type);
		//console.log(post);
	}

	console.log('site.displayPosts: photosCount = ' + photosCount);

	site.updateBoxCount(photosCount);

	if (photosCount)
	{	
		boxes = $(placeholder.html());
	}

	return boxes;
};

site.getBoxCount = function() {
	var len = $('#dashboard').children().length;
	return parseInt(len);	
};

site.updateBoxCount = function(i) {
	var len = site.getBoxCount() + i;
	$('#item-counter').html(len);
};

site.onLoginSuccess = function(data) {
	console.log('site.onLoginSuccess');

	var user = data.response.user;
	var blogCount = user.blogs.length;
	var followingCount = user.following;
	var likeCount = user.likes;

	$('.auth-true').removeClass('hide');
	$('.auth-false').addClass('hide');

	$('#user-name').html(user.name);

	site.getDashboard({}, {
		onSuccess: function(boxes) {
			if (boxes) {				
				console.log('Appending new boxes.');
				boxes.appendTo('#dashboard');				
			} else {
				console.log('No new boxes.');
			}
		},
		onFailure: function() {
			console.log('site.onLoginSuccess: dashboard failed.');
		}
	});
};

site.onTumblrResponse = function(outcome, jsonStr) {
	console.log('site.onTumblrResponse');
	var data = $.parseJSON(jsonStr);
	if (typeof site.onAfterTumblrResponse === 'function') {
		site.onAfterTumblrResponse(outcome, data);
	}
};

site.popupWindow = function(url, title, w, h) {
    var left = (screen.width / 2) - (w / 2);
    var top = (screen.height / 2) - (h / 2);

    var settings = 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left;

    return window.open(url, title, settings);
};