Essence
=======

Essence is a simple PHP library to extract media informations from websites.

[![Build Status](https://secure.travis-ci.org/felixgirault/essence.png)](http://travis-ci.org/felixgirault/essence)

Example
-------

Essence is designed to be really easy to use.
Using the main class of the library, you can retrieve informations in just those few lines:

```php
<?php

require_once 'path/to/essence/bootstrap.php';

$Essence = new fg\Essence\Essence( );

$Media = $Essence->embed( 'http://www.youtube.com/watch?v=39e3KYAmXK4' );

if ( $Media ) {
	// That's all, you're good to go !
}

?>
```

Then, just do anything you want with the data:

```php
<article>
	<header>
		<h1><?php echo $Media->title; ?></h1>
		<p>By <?php echo $Media->authorName; ?></p>
	</header>

	<div class="player">
		<?php echo $Media->html; ?>
	</div>
</article>
```

What you get
------------

With Essence, you will mainly interact with Media objects.
Media is a simple container for all the informations that are fetched from an URL.

Here is the default properties it provides:

* type
* version
* url
* title
* description
* authorName
* authorUrl
* providerName
* providerUrl
* cacheAge
* thumbnailUrl
* thumbnailWith
* thumbnailHeight
* html
* width
* height

These properties were gathered from the OEmbed and OpenGraph specifications, and merged together in a united interface.
Therefore, based on such standards, these properties are a solid starting point.

However, some providers could also provide some other properties that you want to get.
Don't worry, all these "non-standard" properties can also be stored in a Media object.

```php
<?php

if ( !$Media->hasCustomProperty( 'foo' )) {
	$Media->setCustomProperty( 'foo', 'bar' );
}

$value = $Media->getCustomProperty( 'foo' );

?>
```

Configuration
-------------

If you know which providers you will have to query, or simply want to exclude some of them, you can tell Essence which ones you want to use:

```php
<?php

$Essence = new fg\Essence\Essence(
	array(
		'OEmbed/Youtube',
		'OEmbed/Dailymotion',
		'OpenGraph/Ted',
		'YourCustomProvider'
	)
);

?>
```

When given an array of providers, the constructor might throw an exception if a provider couldn't be found or loaded.
If you want to make your code rock solid, you should better wrap that up in a try/catch statement:

```php
<?php

try {
	$Essence = new fg\Essence\Essence( array( ... ));
} catch ( fg\Essence\Exception $Exception ) {
	...
}

?>
```

Advanced usage
--------------

The Essence class provides some useful utility function to ensure you will get some informations.

First, the extract( ) method lets you extract embeddable URLs from a web page.
For example, say you want to get the URL of all videos in a blog post:

```php
<?php

$urls = $Essence->extract( 'http://www.blog.com/article' );

/**
 *	$urls now contains all URLs that can be extracted by Essence:
 *
 *	array(
 *		'http://www.youtube.com/watch?v=123456'
 *		'http://www.dailymotion.com/video/a1b2c_lolcat-fun'
 *	)
 */

?>
```

Now that you've got those URLs, there is a good chance you want to embed them:

```php
<?php

$medias = $Essence->embedAll( $urls );

/**
 *	$medias contains an array of Media objects indexed by URL:
 *
 *	array(
 *		'http://www.youtube.com/watch?v=123456' => Media( ... )
 *		'http://www.dailymotion.com/video/a1b2c_lolcat-fun' => Media( ... )
 *	)
 */

?>
```

Thanks to [Peter Niederlag](https://github.com/t3dev "t3dev on github"), it is now possible to pass some options to the providers.

For example, OEmbed providers accepts the `maxwidth` and `maxheight` parameters, as specified in the OEmbed spec.
Other providers will just ignore the options they don't handle.

```php
<?php

$Media = $Essence->embed(
	'http://www.youtube.com/watch?v=abcdef',
	array(
		'maxwidth' => 800,
		'maxheight' => 600
	)
);

$medias = $Essence->embedAll(
	array(
		'http://www.youtube.com/watch?v=abcdef',
		'http://www.youtube.com/watch?v=123456'
	),
	array(
		'maxwidth' => 800,
		'maxheight' => 600
	)
);

?>
```

Error handling
--------------

By default, Essence does all the dirty stuff for you by catching all internal exceptions, so you just have to test if an Media object is valid.
But, in case you want more informations about an error, Essence keeps exceptions warm, and lets you access all of them:

```php
<?php

$Media = $Essence->embed( 'http://www.youtube.com/watch?v=oHg5SJYRHA0' );

if ( !$Media ) {
	$Exception = $Essence->lastError( );

	echo 'That\'s why you should never trust a camel: ', $Exception->getMessage( );
}

?>
```
