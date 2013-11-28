Sitemap
=======

A Sitemap php library to create sitemaps after convetions like Sitemaps.org or Google or others.


Introduction
------------

I found most of the sitemap classes out there more quick and dirty than I wanted it for my projects.
That is why, I created a new one and tried to keep it as clean as I could.

Also most of the sitemaps out there had a lack of support for image, video or news sitemaps of Google which are
included in this first release of Sitemap.

Contribution
------------

This sitemap class is not perfect (see TODO), but with your help, comments and issue reports I hope this one will
get better and better. At least that is what I am hoping to reach with releasing it here ;)

The most urgent parts of the code that need work are almost always commented with inline comments prefixed
by __@TODO__ (also in the TODO-Section) and __@XXX__. Where TODO is clear, XXX means, that there is maybe a 
part of the code where my brain thought "there must be a better way", but I could not figure out one at that very
moment. Sometimes they are just a first idea for a todo to solve.
So, please feel free to open an issue for these comments if you have any ideas regarding them.


Installation
------------

It is pretty simple.

You just need to **include** or **require** the class you need. 
For example, you want to create a Google Video Sitemap, use: 
```php
  include 'lib/Sitemap/SitemapsOrg/Google/Video.php'
  
  $videoSitemap = new Sitemap_SitemapsOrg_Google_Video();
```

TODO
----

There some todos in the source code and also some annotations as mentioned above.

### SitemapInterface

- Tests for the sitemap to make it easiert to test new features.
- Add possibility to provide other entries data to make conditional validation possible.
- Some more abstraction is needed in the interface.

### Sitemap_SitemapsOrg

- Calculation of estimated filesize so the given limits can be maintained.
- Consider! automatic output of multiple files if limits are reached.

### Sitemap_SitemapsOrg_Google

- Add support for attributes

The long term goals of this project are to provide universal support for HTML and XML sitemaps or whatever sitemap format
there will be in the future.


License
-------

Sitemap is licensed under GPLv2.

Every tool or library included in it may be licensed under its own license.
