# Button PDF (WordPress Plugin)

Create customizable PDF buttons with personalized links via a custom post type. Easily insert the generated buttons into your posts or pages using a simple shortcode.

## Table of Contents

- [Description](#description)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Shortcode](#shortcode)
- [Uninstall](#uninstall)
- [FAQ](#faq)
- [Contributing](#contributing)
- [License](#license)

---

## Description

**Button PDF** is a WordPress plugin that registers a Custom Post Type (CPT) for generating PDF buttons. Each button stores a URL to a PDF file and can be inserted into any post or page using a shortcode. The plugin provides a simple interface in the WordPress admin to manage your PDF button links, titles, and more.  

## Features

- Registers a **Custom Post Type** named "Botón PDF" (PDF Button).
- Adds a **Metabox** for the PDF URL plus a **Media Library** picker (PDF mime type).
- **Gutenberg block** “Botón PDF” (`mi-boton-pdf/boton`) with live preview and the same options as the shortcode.
- **Shortcode** to embed the button in the classic editor or anywhere shortcodes run.
- List table columns: **URL del PDF** (clickable) and **Shortcode** (readonly field for quick copy).
- Default **SVG** icon, responsive **size** option, optional **`download`** hint, and **`target`** for new/same tab.
- Translation template: `languages/mi-boton-pdf.pot`.
- **Uninstall**: `uninstall.php` removes all `mbpdf_button` posts when the plugin is deleted from the Plugins screen.

## Requirements

- WordPress **5.8** or higher (block editor + server-side preview; shortcode still works on older sites if you only use the shortcode—5.8+ recommended).
- PHP **7.4** or higher

## Installation

1. **Download or Clone** this repository.
2. Upload the entire `mi-boton-pdf` folder to your `/wp-content/plugins/` directory.
3. In the WordPress admin area, go to **Plugins** and find **Button PDF**.
4. Click **Activate**.

Alternatively, you can zip the folder and install it through **Plugins > Add New > Upload Plugin** in your WordPress admin area.

## Usage

1. In your WordPress admin menu, look for **Botones PDF** (or "PDF Buttons").
2. Click **Add New** to create a new PDF button.
3. Enter a **Title** (this title will be displayed as the button text on the site).
4. In the metabox **URL del PDF**, paste the file URL or click **Biblioteca de medios…** to pick a PDF.
5. Publish your new button.

### Block editor (Gutenberg)

Add the **Botón PDF** block (Widgets category), then choose your button in the sidebar. You can override text, open in a new tab, control the `download` attribute, and icon size.

## Shortcode

Use in any post, page, or widget that processes shortcodes:

```text
[mbpdf_boton id="123"]
```

### Optional attributes

| Attribute   | Default   | Description |
|------------|-----------|-------------|
| `id`       | —         | **Required.** ID of the PDF button (CPT). |
| `text`     | *(title)* | Label shown under the icon. |
| `class`    | —         | Extra CSS classes on the wrapper. |
| `target`   | `_blank`  | `_blank` (new tab) or `_self` (same tab). |
| `download` | `auto`    | `auto` (adds `download` for same-site URLs), `yes`, or `no`. |
| `size`     | `48`      | Icon size in pixels (16–128). |

Example:

```text
[mbpdf_boton id="123" target="_self" download="yes" size="40" text="Descargar folleto"]
```

## Uninstall

Deleting the plugin from **Plugins → Installed Plugins** runs `uninstall.php`, which **permanently deletes** all Botones PDF entries. Back up first if you need to keep them.

## FAQ

**PDFs uploaded as a generic file type do not appear in the media modal.**  
You can still paste the file URL manually in the metabox.

## Contributing

Pull requests and issues are welcome on the repository.

## License

See `LICENSE` in this package.
