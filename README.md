# Timeline External Module for REDCap

This REDCap module implements a plugin page to display a record data as a dynamic timeline where you can easily navigate by dragging and scrolling in the Timeline.

Timeline uses `visjs` (https://visjs.org/) timeline library (https://github.com/visjs/vis-timeline).

# Manual installation

- Clone this repository in the redcap `modules` directory.
- rename the Timeline plugin to fit modules folders convention (ex: timeline_v2.0.0)
- enable the module in the REDCap control center

# Configuration

Timeline has to be configured for each project as a json.

Go to the left `Application` panel and click on `External Modules` to set the json configuration.

## Json configuration structure

The configuration consist in an object with 2 properties:

- groups
- data

### groups

`groups` is an array of group objects displaying items grouped as an horizontal track in the timeline.

It contains 3 properties:

- id : an id (numeric?)
- content : the label of the group
- order : a number to specify in which order the groups are displayed (ascending top to bottom)

### data

`data` is an object defining the items you will put in your `groups` tracks.

Each `data` key defines a repeat instance identified by creating a concatenation of `<redcap_event_name>_<redcap_repeat_instrument>`.
Do not forget the underscore in between.
If any part is absent because of either no event or no repeat used in a project, an empty string is used for concatenation.
For instance, for elements from a not repeated instrument without events, the concatenated identifier is `_`.

The associated value is an array of objects with the following properties:

- `group_field` which must match one of the `content` in `groups` to specify in which track group the item will be displayed
- the content of the item is defined via the `content_format` property according to the values list defined in the `fields` property. The order of the fields must match the `%s` formatting order.
- `start_field` and `end_field` should match REDCap date fields of your project

NOTE: the values are displayed as labels (event, instruments names, ...)

### Example of json configuration for a project without event but with repeats of `Biological Samples` and `Treatments` instruments:

```
{
    "groups": [
        {
            "id":"0",
            "content":"Available samples",
            "order":"1"
        },
        {
            "id":"1",
            "content":"Register a Patient",
            "order":"7"
        },
        {
            "id":"2",
            "content":"Treatment",
            "order":"4"
        }
    ],
    "data":{
        "_": [
            {
                "group_field":"Register a Patient",
                "fields":[
                    "patient_birthdate"
                ],
                "content_format":"Birth: %s",
                "start_field":"patient_birthdate",
                "end_field":"patient_birthdate"
            },
            {
                "group_field":"Register a Patient",
                "fields":[
                    "patient_first_visit_date",
                    "patient_first_visit_service"
                ],
                "content_format":"Visit on %s at %s",
                "start_field":"patient_first_visit_date",
                "end_field":"patient_first_visit_date"
            }
        ],
        "_Biological Samples": [
            {
                "group_field":"Available samples",
                "fields":[
                    "biologicalsample_sampleid",
                    "biologicalsample_collectdate"
                ],
                "content_format":"Stock: %s on %s",
                "start_field":"biologicalsample_collectdate",
                "end_field":"biologicalsample_collectdate"
            }
        ]
        "_Analysis": [
            {
                "group_field":"Treatment",
                "fields":[
                    "treatment_type",
                    "treatment_cycle",
                    "treatment_start_date",
                    "treatment_end_date"
                ],
                "content_format":"%s : %s",
                "start_field":"treatment_start_date",
                "end_field":"treatment_end_date"
            }
        ]
    }
}
```

# Navigation

- left click dragging to move left and right
- mouse wheel scroll to zoom
- click item to highlight
