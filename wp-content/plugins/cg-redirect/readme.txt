=== CG-Redirect ===
Tags: automatic redirection
Contributors: davebytes

CG-Redirect is really simple.  It's a tag you can enter in a post, so when that individual post
page is loaded, the browser will be redirected to another URL.


== Installation ==

1. Upload [cg-redirect.php] to your Plugins folder (wp-content/plugins/).
2. From the Admin interface, go to Plugins, and activate the CG-Redirect plugin.
3. Start using redirects! ;)


== Example Use ==

In the simplest case, let's imagine I want a Page on my sidebar that links to my homepage.
So, I create a new Page, title it Home, and then in the content all I need to do is:

  <redirect http://www.chait.net />

That's it.  I save the Page, and then when I click on that entry in the Pages list in my
sidebar, under the covers I'm redirected to the URL I entered.

As you can see, the format of the CG-Redirect tag is simply:

  <redirect {SOME URL} />
  
That {SOME URL} should be fully specified -- that is, have the http:// on the front.  This is
because you could redirect someone to an ftp://, or you could even use it with something like
mailto:user@domain.com (it's weird for the browser, but the final result is a launched email
with the mail address filled in...).


== Quick brainstorming ==

- As mentioned, great for a 'Home' Page link.
- Jump to a Forum script, even on another site.
- Jump to a Gallery script, again could be elsewhere (flickr, snapfish...).
- Have a news posting with an excerpt on the homepage, that when clicked goes to the original
site.  Re-blog scripts could be tweaked to auto-generate the redirect tag and URL...
- Good for having a Post or Page that redirects to a custom Contact Form.
- and much much more!  All for the low price of $0.00!


== How it works ==

CG-Redirect purposefully hooks itself as late in the page-startup process at it can, so that any
other plugins that need to do 'magic' (pageload tracking, etc.) can do so.  Right before the page
template gets loaded, CG-Redirect gets called, and if a single post or page is being loaded, it
looks at the content to see if our redirect tag exists.  If so, it immediately sends back headers
to the browser to redirect to the specified URL.

Note that other plugins that hook the ACTUAL page generation (that is, the wp_head/wp_footer
type stuff) will not be triggered.  It's possible if this is a desired feature, I could buffer
the entire page generation, toss the results, THEN redirect.  But that's potentially slow, and
you actually might NOT want those plugins to trigger in this instance.


== Support ==

If you have questions related to CG-Redirect, or ideas for improvements, you can post
in the WordPress forums, contact me through my site (http://www.chait.net), or email me
directly at cgcode @ chait.net.


== Frequently Asked Questions ==

= Why use CG-Redirect? =

It's a quick way to have Pages that link to other systems on your site, or have Posts that can
jump to external articles, with a simple one-line tag (and you'd have to type in that URL
somewhere anyway!).

= Why don't I just use a meta-refresh? =

By not physically embedding a meta-refresh or such into a post/page, you can go back in and
edit the post at any time.  Otherwise, editing the post typically ends up triggering the
refresh, and you are bounced away.

= What else is cool about CG-Redirect? ==

By hooking into the single-post/page load itself, we don't interfere with normal 'front page'
functioning of a site.   This means that you can still have an excerpt for a post, or a <more> tag,
and that will all still display properly, normally, on the homepage or any archive views.  Only
when clicking into the post (through the title, permalink, sidebar links, whatever) does the
redirect get triggered.
