phpDocumentor Markdown export
=============================

This is a script that can generate markdown (.md) documentation files from your DocBlock comments.

It is tailored for projects using PSR-0, PSR-1, PSR-2, PSR-4 and namespaces.

This is based on the work of Evert Pot in https://github.com/evert/phpdoc-md.

**Note!** This package has been superseded by https://github.com/cvuorinen/phpdoc-markdown-public

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
