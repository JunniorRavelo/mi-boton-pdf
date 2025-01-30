# Button PDF (WordPress Plugin)

Create customizable PDF buttons with personalized links via a custom post type. Easily insert the generated buttons into your posts or pages using a simple shortcode.

## Table of Contents

- [Description](#description)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Shortcode Example](#shortcode-example)
- [FAQ](#faq)
- [Contributing](#contributing)
- [License](#license)

---

## Description

**Button PDF** is a WordPress plugin that registers a Custom Post Type (CPT) for generating PDF buttons. Each button stores a URL to a PDF file and can be inserted into any post or page using a shortcode. The plugin provides a simple interface in the WordPress admin to manage your PDF button links, titles, and more.  

## Features

- Registers a **Custom Post Type** named "Botón PDF" (PDF Button).
- Adds a **Metabox** for inserting the PDF URL.
- Generates a **Shortcode** to embed a clickable PDF button.
- Displays the shortcode in a custom column on the CPT list screen.
- Includes a default **SVG icon** and styling for your PDF button.

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher

## Installation

1. **Download or Clone** this repository.
2. Upload the entire `button-pdf` folder to your `/wp-content/plugins/` directory.
3. In the WordPress admin area, go to **Plugins** and find **Button PDF**.
4. Click **Activate**.

Alternatively, you can zip the folder and install it through **Plugins > Add New > Upload Plugin** in your WordPress admin area.

## Usage

1. In your WordPress admin menu, look for **Botones PDF** (or "PDF Buttons").
2. Click **Add New** to create a new PDF button.
3. Enter a **Title** (this title will be displayed as the button text on the site).
4. In the metabox labeled "**URL del PDF**," provide the URL of the PDF file.
5. Publish your new button.

## Shortcode Example

Inside a post or page editor, use:

```html
[mbpdf_boton id="123"]
