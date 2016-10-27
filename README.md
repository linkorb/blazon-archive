Blazon: Static Site Generator
=============================

## Features:

* Generates static HTML files
* Write pages in Markdown
* GFM (Github Flavored Markdown)
* Preview web-server for local editing
* Twig templates
* Assets

## Usage:

0. Create a new directory (or repository)
0. Add a file called `blazon.yml`
0. Create a `build/` directory in your repository (optionally add this directory to .gitignore)
0. Initialize the build directory with static assets from `/static/assets`:
    ```sh
        blazon init
    ```
0. Start the preview webserver:
    ```sh
        blazon serve
    ```
0. Open the following URL in your webbrowser: [http://127.0.0.1:8080](http://127.0.0.1:8080)

The preview server re-generates the site on every request.

Once you're happy with your new site, simply upload the static files in the `build/` directory to your webserver or github pages.

## License

MIT. Please refer to the [license file](LICENSE.md) for details.

## Brought to you by the LinkORB Engineering team

<img src="http://www.linkorb.com/d/meta/tier1/images/linkorbengineering-logo.png" width="200px" /><br />
Check out our other projects at [linkorb.com/engineering](http://www.linkorb.com/engineering).

Btw, we're hiring!
