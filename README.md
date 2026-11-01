# Simulic labs filter for Moodle

Paste a [Simulic](https://simulic.com) class link into any Moodle text, and students see the interactive lab right there, with no account.

Simulic is a library of interactive science and maths labs. This filter means teachers no longer need to paste iframe code into the editor's HTML view, and administrators no longer need to relax HTML cleaning for them.

## What it does

The filter finds links of the form `https://simulic.com/p/CODE` and shows the lab in a frame that grows to fit it. It works wherever Moodle applies filters, including Pages, Text and media areas, labels, assignments and quiz question text.

| What the teacher writes | What students see |
| --- | --- |
| The link pasted on its own | The embedded lab |
| A link with its own wording, such as "Open the lab" | A normal link |
| The link inside `pre`, `code` or a text box | The text as written |

## Requirements

- Moodle 4.1 to 5.3
- A free Simulic teacher account, to create class links. Students need no account, and the filter needs no API key.

Tested on Moodle 4.1, 4.5 and 5.3.

## Install

1. Download the ZIP of this repository.
2. Go to **Site administration > Plugins > Install plugins** and upload the ZIP. Or copy the folder to `filter/simulic` (on Moodle 5.1 and later, `public/filter/simulic`), and rename it to `simulic`.
3. Complete the upgrade.
4. Go to **Site administration > Plugins > Filters > Manage filters** and set **Simulic labs** to **On**.

Put the filter above **Convert URLs into links and images** in the list, though it also works below it.

## Use

1. On Simulic, open a lab, click **Share**, create a link and copy it.
2. In Moodle, paste the link into the text and save.

The link keeps the starting values and any questions you set on Simulic. Answers go to your Simulic link, not to the Moodle gradebook.

## Privacy

The filter stores no personal data in Moodle and sends none to Simulic. Each student's browser loads the lab from `simulic.com`. Student pages set no cookies. If a teacher attaches questions to a link, the nickname and answers a student types in the lab go to Simulic. See the [Simulic privacy policy](https://simulic.com/en/privacy/).

## Licence

The filter is free software under the [GNU GPL v3 or later](LICENSE). The labs themselves are a hosted service from Simulic and are not part of this repository. Pro plans and AI credits are paid, and neither is needed to use this filter.

## Support

Report problems in [GitHub Issues](https://github.com/bongvd/moodle-filter_simulic/issues).
