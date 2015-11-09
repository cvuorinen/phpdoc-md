phpDocumentor Markdown export
=============================

This is a script that can generate markdown (.md) documentation files from your DocBlock comments.

It is tailored for projects using PSR-0, PSR-1, PSR-2, PSR-4 and namespaces.

This is based on the work of Evert Pot in https://github.com/evert/phpdoc-md.

Comment from Evert's original README:
> The code is ugly, it was intended as a one-off, and I was in a hurry.. so the codebase may not be up to your standards. (it certainly isn't up to mine).

I haven't much improved the code, at least not yet. Just modified it to better suit my use case.

Main differences
----------------

Main differences with the original is that the goal is not to produce a full blown API doc.
But rather a usage documentation for a small library to document only it's public API.
So it skips all abstract classes and interfaces and non-public methods.
And only creates a single file with the index as well as all the content (although there is a setting to
generate multiple files like the original).

Installation
------------

Install with composer:

```bash
composer require cvuorinen/phpdoc-md
```


Usage
-----

First ensure that phpDocumentor 2 is installed somewhere, after, you must
generate a file called `structure.xml`.

The easiest is to create a temporary directory, for example named `docs/`.

    # phpdoc command
    phpdoc  -d [project path] -t docs/ --template="xml"

    # Next, run phpdocmd:
    phpdocmd docs/structure.xml [outputdir]

Options
-------

    --lt [template]
        This specifies the 'template' for links we're generating. By default
        this is "%c.md".

    --title [title]
        This specifies the title for the generated Markdown document.
