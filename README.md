# Portfolio Generator
Work in progress

## Change Log
### Version 1.1
* remove silex 

## Installation
**Required PHP 7**

```bash
git clone https://github.com/Modius22/portfolio.git MyPortfolio
```
or
```bash
composer create-project modius22/portfolio MyPortfolio
```
## Basic Usage
```bash
cd MyPortfolio
composer install
php -S localhost:8090
```

Now open **MyPortfolio** in your favorite IDE and create a **meta.json**-file in the **resources**-directory.
This file contains all information of your portfolio.

### meta.json
The **sections**-property contains every section of your portfolio.
The **key** is the section title and the **value** is the section content (=object).
For example a *about me* and *skills*-section looks like
```json
{
  "sections": {
    "About me": {
      "description": "Hello my name is ..."
    },
    "Skills": {
      "description": "<ul><li>Coding</li><li>CSS</li></ul>"
    }    
  }
} 
```
Open your browser and go to **localhost:8090**, you should see a heading with the text *About me* and a paragraph with *Hello my name is* and a heading with *Skills* and a listing with *Coding* and *CSS* 

**Note:** The default template uses the Bootstrap framework, jQuery and chart.js from CDN. The **description-property is parsed by Twig**

## Section Templates
HTML in JSON-files is a bit messy, so you should use templates. \
First, add the **template**-property to your *About me*-section and set it to the path to the template:
```json
{
  "sections": {
    "About me": {
      "template": "sections/about_me.html.twig"
    }
  }
}
```
Next, create in the **resources**-directory a directory with the name **sections** and in this create the **about_me.html.twig** with this content:
```twig
<h1>{{ heading }}</h1>
Hello my name is ...
```
**Note:** heading is the key of the section-array. It can be overwritten with the **heading**-property

### Variables
You can also add variables in your meta.json sections which you can use in your template. \
For example, add the **name**-property with your name to the **About me** section and in your template replace "*...*" with ``{{ section.name }}``

**Note:** The ``section``-Variable contains all section-properties


## Resources (images, css...)
For additional resources you can add the **resources**-property in the **meta.json** and call the **resource-twig-function**
```json
{
    "resources": {
        "octocat": "https://assets-cdn.github.com/favicon.ico",
        "internal": "@hello.png"
    }
}
```
In your template:
```twig
<img src="{{ resource('octocat') }}">
```
The resource-function accepts 3 parameters:
1. resource-name (key in resources)
2. embed the resource (loads the file an writes into the html code)
3. encode the embedded source as base64 (useful to embed images)

If the resource-path starts with **@** the file is loaded from the resources-directory

## Custom Template
To override the base template, create a **template.custom.html.twig** in the resources directory.

## German Tutorial
Part 1: http://www.modius-techblog.de/programmierung/eigene-online-visitenkarte-richtig-erstellen/
Part 2: http://www.modius-techblog.de/programmierung/online-visitenkarte-die-erste-sektion-mit-twig/
Part 3: http://www.modius-techblog.de/programmierung/online-visitenkarte-aktueller-job-mit-progress-bar-darstellen/

## Example Site

The portfolio from Christian Piazzi (Modius22): http://www.christian.piazzi.org

## Contribute

Feel free to fork this repository and create pull-requests
