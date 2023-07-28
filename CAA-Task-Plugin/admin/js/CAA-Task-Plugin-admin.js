// @ts-ignore
const $ = jQuery

/**
 * A collection of utility functions.
 *
 * @since 1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/admin
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class Util {
  /**
   * Parses a string into an ID number. Throws {Error} if failed to parse, or number is negative.
   * @param {string} id_str
   * @returns {number} the id in number form
   */
  static parseID(id_str) {
    let id_num = parseInt(id_str)

    if (isNaN(id_num)) {
      throw new Error("ID is Not a Number")
    }
    if (id_num < 0) {
      throw new Error("ID is negative")
    }

    return id_num
  }
}

/**
 * Used to draw cells in a specific column in the table.
 *
 * @since 1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/admin
 * @author     Ryan Sauers <sauer319@umn.edu>
 * @see        Table
 */
class Column {
  /**
   * Constructs a column.
   * @since 1.0.0
   * @param {string} header The text in the header of the column
   * @param {(row_datum:any) => string | HTMLElement} draw_callback is the callback used to draw information in every cell of the column using some row datum.
   **/
  constructor(header, draw_callback) {
    this.header = header
    this.draw_callback = draw_callback
  }

  /**
   * @since 1.0.0
   * @returns {string} the text in the header column
   */
  get_header() {
    return this.header
  }

  /**
   * Creates a cell for the column.
   * @since 1.0.0
   * @param {any} row_datum the datum for the row of the table.
   * @returns {HTMLTableCellElement} the cell for the row in the column.
   */
  create_cell(row_datum) {
    const cell_internals = this.draw_callback(row_datum)
    const $td = $("<td>")

    if (cell_internals instanceof String) {
      $td.html(cell_internals)
    } else {
      $td.append(cell_internals)
    }

    return $td[0]
  }
}

/**
 * Aids the process of drawing tables.
 *
 * @since 1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/admin
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class Table {
  /**
   * Creates an empty Table.
   * @since 1.0.0
   */
  constructor() {
    this.columns = []
  }

  /**
   * Adds a column to the Table.
   * @since 1.0.0
   * @param {string} header is the text in the header of the column
   * @param {(row_datum: any) => HTMLElement | string} draw_callback is the callback that draws a cell
   */
  add_column(header, draw_callback) {
    this.columns.push(new Column(header, draw_callback))
  }

  /**
   * Sets the callback that is called on every row after it is made.
   * @since 1.0.0
   * @param {(row_datum: any, row: HTMLTableRowElement) => void | undefined} row_callback is the callback to call on the row.
   */
  set_row_callback(row_callback) {
    this.row_callback = row_callback
  }

  /**
   * Empties the container and draws a table based on the given data.
   * @since 1.0.0
   * @param {HTMLElement} container is the container to draw the table in.
   * @param {any[]} data is the table data, each entry contains information about a row.
   */
  draw(container, data) {
    const $container = $(container).html("") // empties container
    const $table = $("<table>").appendTo($container)
    const $tr_head = $("<tr>").appendTo($("<thead>").appendTo($table))
    const $tbody = $("<tbody>").appendTo($table)

    // draw headers
    for (let column of this.columns) {
      $("<th>")
        .appendTo($tr_head)
        .html(column.get_header())
    }

    // draw cells
    const rowdata = Object.values(data)
    if (rowdata.length > 0) {
      for (let row_datum of rowdata) {
        $tbody.append(this.create_row(row_datum))
      }
    }
  }

  /**
   * Creates an HTML row
   * @since 1.0.0
   * @param {any} row_datum is the data for a row in the table
   * @returns {HTMLTableRowElement} the table row
   */
  create_row(row_datum) {
    var $tr = $("<tr>")

    for (let column of this.columns) {
      $tr.append(column.create_cell(row_datum))
    }

    if (this.row_callback) {
      this.row_callback(row_datum, $tr[0])
    }

    return $tr[0]
  }
}

/**
 * Form Fields are used to draw fields in forms more cleanly.
 *
 * @todo the field_config input in the constructor might work better if it has an attributes field
 * @since 1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/admin
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class Form_Field {
  /**
   * Constructs the form field based on a config object.
   * @param {string} name is the name of the field (this is the key of the form output)
   * @param {Function} $ is a reference to jQuery
   *
   * @param {Object} [field_config={}] is an object to describe the configuration for fields
   * @param {string} [field_config.input_type] is the 'type' for the input element, if this is set the
   * field will be an input element (cannot be set with field_config.tag_name)
   * @param {string} [field_config.tag_name] is the tagname for the tag, if this is set the field will
   * be an element with the specified tag name (cannot be set with field_config.input_type)
   * @param {boolean} [field_config.linebreak] specifies whether there should be a linebreak after the field
   * @param {string} [field_config.default] is the default value for the field element
   * @param {boolean} [required] specifies whether or not the field is required to be filled
   * @param {string} [placeholder] is a placeholder for the field
   *
   * @param {Object} [label_config] if this is defined, a label will be drawn before the field
   * @param {string} [label_config.name] is the text for the label
   * @param {boolean} [label_config.linebreak] if this is true, there will be a line break after the label
   *
   */
  constructor(name, $, field_config = {}, label_config) {
    // default for input field
    const default_config_input = {
      label: {
        name: name,
        linebreak: false
      },
      field: {
        input_type: "text",
        linebreak: true,
        default: "",
        placeholder: "",
        required: false
      }
    }

    // default for tag field
    const default_config_tag = {
      label: {
        name: default_config_input.label.name,
        linebreak: true
      },
      field: {
        tag_name: "textarea",
        linebreak: default_config_input.field.linebreak,
        default: default_config_input.field.default,
        required: default_config_input.field.required
      }
    }

    // by default uses default_config_input.
    // uses default_config_tag if 'tag_name' is set.
    const default_config = field_config["tag_name"]
      ? default_config_tag
      : default_config_input

    // copy defaults to config.field fields
    for (var key in default_config.field) {
      if (field_config[key] === undefined) {
        field_config[key] = default_config.field[key]
      }
    }

    // draw the label if it was defined in config
    if (label_config) {
      // copy defaults to undefined config.label fields
      for (var key in default_config.label) {
        if (label_config[key] === undefined) {
          label_config[key] = default_config.label[key]
        }
      }

      // create label
      const $label = $(`<label>${label_config.name}</label>`)
      $label.addClass("task-definition-label")
      $label.css("display", label_config.linebreak ? "block" : "inline-block")
      this.label = $label[0]
    }

    // the default config is the default, or matches the config passed in by user
    if (field_config["tag_name"]) {
      this.field = this.create_tag_element(name, field_config)
    } else {
      this.field = this.create_input_element(name, field_config)
    }
  }

  /**
   * Creates an html element with the tag specified by field_config
   * @param {string} name is the name of the field
   * @param field_config
   * @returns {HTMLElement} an html element with the tag specified by field_config
   */
  create_tag_element(name, field_config) {
    const $tag = $(
      `<${field_config["tag_name"]}>${field_config["default"]}</${field_config["tag_name"]}>`
    )
    $tag.attr("name", name)
    $tag.attr("required", field_config["required"])
    $tag.css("display", field_config["linebreak"] ? "block" : "inline-block")
    return $tag[0]
  }

  /**
   * Creates an input element with the settings specified by field_config
   * @param {string} name is the name of the field
   * @param field_config
   * @returns {HTMLElement} an html element with the tag specified by field_config
   */
  create_input_element(name, field_config) {
    const $field = $("<input/>")
    $field.attr("name", name)
    $field.attr("type", field_config["input_type"])
    $field.attr("value", field_config["default"])
    $field.attr("placeholder", field_config["placeholder"])
    $field.attr("required", field_config["required"])
    $field.css("display", field_config["linebreak"] ? "block" : "inline-block")
    return $field[0]
  }

  /**
   * @since 1.0.0
   * @returns true if there is a label associated with the field, false otherwise
   */
  has_label() {
    return !!this.label
  }

  /**
   * @since 1.0.0
   * @returns the label element or null if there is no label element
   */
  get_label() {
    return this.label
  }

  /**
   * @since 1.0.0
   * @returns the field element
   */
  get_field() {
    return this.field
  }

  /**
   * Appends the label and field to the container
   * @since 1.0.0
   * @param {HTMLElement} container is the container to append the form field to
   */
  append_to(container) {
    if (this.has_label()) {
      $(container).append(this.get_label())
    }
    $(container).append(this.get_field())
  }
}

/**
 * This class is responsible for drawing the popup task definition editor and handling its submission.
 *
 * @todo task definition data is currently typed as 'any'
 * @todo on form submit, there may be errors regarding hitting 'Enter' rather than pressing a button
 * @since 1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/admin
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class Task_Definition_Editor {
  /**
   * The id for the container where task definitions are displayed.
   * @since 1.0.0
   * @type {string}
   */
  static TASK_DEFINITION_EDITOR_CONTAINER_ID =
    "task-definition-editor-container"

  /**
   * Opens up the popup menu to edit task definitions.
   * @since 1.0.0
   * @param {number} [task_definition_id] The id of the task definition that is being edited
   */
  static async open(task_definition_id) {
    this.draw(Event_Type_Editor_Page.get_event_type_id(), task_definition_id)
  }

  /**
   * Exits the task definition editor popup.
   * @since 1.0.0
   */
  static async exit() {
    $(`#${this.TASK_DEFINITION_EDITOR_CONTAINER_ID}`).remove()
  }

  /**
   * Creates the form element for the task definition editor.
   * @since 1.0.0
   * @param {number} [task_definition_id] the id of the task definition (if it exists)
   * @param {any} [task_definition_data] the data associated with the task definition (if it exists)
   * @return {HTMLFormElement}
   */
  static create_task_definition_editor_form(
    task_definition_id,
    task_definition_data
  ) {
    const form = $("<form>")[0]

    // title field
    const title_field = new Form_Field(
      "title",
      $,
      {
        default: task_definition_id ? task_definition_data.title : undefined
      },
      { name: "Title" }
    )
    title_field.append_to(form)

    // start offset field
    const start_offset_field = new Form_Field(
      "start_offset",
      $,
      {
        input_type: "number",
        default: task_definition_id
          ? task_definition_data.start_offset_in_days
          : undefined
      },
      { name: "Start Offset" }
    )
    $(start_offset_field.get_field())
      .attr("step", "1")
      .attr("min", "0")
    start_offset_field.append_to(form)

    // finish offset field
    const finish_offset_field = new Form_Field(
      "finish_offset",
      $,
      {
        input_type: "number",
        default: task_definition_id
          ? task_definition_data.finish_offset_in_days
          : undefined
      },
      { name: "Finish Offset" }
    )
    $(finish_offset_field.get_field())
      .attr("step", "1")
      .attr("min", "0")
    finish_offset_field.append_to(form)

    // description field
    const description_field = new Form_Field(
      "description",
      $,
      {
        tag_name: "textarea",
        default: task_definition_id
          ? task_definition_data.description
          : undefined
      },
      { name: "Description" }
    )
    $(description_field.get_field())
      .attr("cols", "100")
      .attr("rows", "10")
    description_field.append_to(form)

    // task_definition_id field
    if (task_definition_id) {
      const id_field = new Form_Field("task_definition_id", $, {
        default: task_definition_id.toString()
      })
      $(id_field.get_field()).hide()
      id_field.append_to(form)
    }

    // submit buttion
    const submit_button = new Form_Field(
      task_definition_id ? "Save" : "Create",
      $,
      {
        input_type: "submit",
        linebreak: false,
        default: task_definition_id ? "Save" : "Create"
      }
    )
    submit_button.append_to(form)

    // cancel button
    const cancel_button = new Form_Field("cancel", $, {
      input_type: "submit",
      linebreak: false,
      default: "Cancel"
    })
    cancel_button.append_to(form)

    return form
  }

  /**
   * Handles the submittion of the task definition editor form.
   * @since 1.0.0
   * @param {number} event_type_id is the id of the event type containing the task definition
   * @param {SubmitEvent} evt is form submit event
   */
  static async handle_task_definition_editor_form_submit(evt, event_type_id) {
    evt.preventDefault()

    const formData = new FormData(
      $(`#${this.TASK_DEFINITION_EDITOR_CONTAINER_ID} > form`)[0]
    )
    const formObj = Object.fromEntries(formData)

    const submit_value = evt.submitter
      ? evt.submitter.getAttribute("value")
      : "Save"

    switch (submit_value) {
      case "Cancel":
        this.exit()

        break
      case "Delete":
        Task_Definition_Requests.remove_task_definition(
          event_type_id,
          Util.parseID(formObj["task_definition_id"].toString())
        )
        this.exit()
        Task_Definition_Table.draw_editable(event_type_id)

        break
      case "Save":
        Task_Definition_Requests.update_task_definition(
          event_type_id,
          Util.parseID(formObj["task_definition_id"].toString()),
          JSON.stringify(formObj)
        )
        this.exit()
        Task_Definition_Table.draw_editable(event_type_id)

        break
      case "Create":
        await Task_Definition_Requests.create_task_definition(
          event_type_id,
          JSON.stringify(formObj)
        )
        this.exit()
        Task_Definition_Table.draw_editable(event_type_id)

        break
      default:
        this.exit()

        break
    }
  }

  /**
   * Draws the popup task definition editor.
   * @since 1.0.0
   * @param {number} event_type_id is the id of the event type containing the task definition
   * @param {number} [task_definition_id] is the id of the task definition that is being edited in the editor
   */
  static async draw(event_type_id, task_definition_id) {
    // create container
    const wrap_div = $(".wrap")[0] // wordpress displays your content in a div with the 'wrap' class.
    const $div = $("<div>").appendTo(wrap_div)
    $div.attr("id", this.TASK_DEFINITION_EDITOR_CONTAINER_ID)

    // create title
    $(
      `<h2>${task_definition_id ? "Edit" : "Create"} Task Definition</h2>`
    ).appendTo($div)

    // get task definition data
    var task_definition_data = task_definition_id
      ? (
          await (
            await Task_Definition_Requests.get_task_definition(
              event_type_id,
              task_definition_id
            )
          ).json()
        ).data
      : null

    // create form
    const form = this.create_task_definition_editor_form(
      task_definition_id,
      task_definition_data
    )
    $div.append(form)
    $(form).on("submit", function(evt) {
      Task_Definition_Editor.handle_task_definition_editor_form_submit(
        evt.originalEvent,
        Event_Type_Editor_Page.get_event_type_id()
      )
    })
  }
}

/**
 * This class is responsible for drawing the table displaying task definitions for an event type. It
 * provides button functionality to remove and edit task definiitons.
 *
 * @todo task definition data is currently typed as 'any'
 * @since 1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/admin
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class Task_Definition_Table {
  /**
   * Gets the task definition id from a button pressed within a table of task definitions.
   *
   * The table row must have the attribute 'task_definition_id', and the button must be located
   * as a child of the <td> tag.
   *
   * @since 1.0.0
   * @param {MouseEvent} evt
   * @returns {number} The task definition id.
   */
  static get_task_definition_id_from_button_click(evt) {
    if (!(evt.target instanceof HTMLButtonElement)) {
      throw new Error(
        "Failed to get task definition id: Element clicked was not button."
      )
    }

    return this.get_task_definition_id_from_button(evt.target)
  }

  /**
   * Gets the task definition id from a button within a table of task definitions.
   *
   * The table row must have the attribute 'task_definition_id', and the button must be located
   * as a child of the <td> tag.
   *
   * @since 1.0.0
   * @param {HTMLButtonElement} btn is the button element in the task definition table
   * @returns {number} The task definition id.
   */
  static get_task_definition_id_from_button(btn) {
    if (
      !(btn.parentElement instanceof HTMLTableCellElement) ||
      !(btn.parentElement.parentElement instanceof HTMLTableRowElement)
    ) {
      throw new Error(
        "Failed to get task definition id: Page layout for table button is incorrect."
      )
    }

    let task_definition_id = btn.parentElement.parentElement.getAttribute(
      "task_definition_id"
    )
    if (task_definition_id === null) {
      throw new Error(
        "Failed to get task definition id: Table row has no attribute task_definition_id"
      )
    }

    return Util.parseID(task_definition_id)
  }

  /**
   * Sets the id of the container for the task definition table. This must be called before the
   * table is drawn.
   * @since 1.0.0
   * @param {string} container_id is the container id for the table
   */
  static set_container_id(container_id) {
    this.TASK_DEFINITIONS_CONTAINER_ID = container_id
  }

  /**
   * @since 1.0.0
   * @returns {string} the container id for the table.
   */
  static get_container_id() {
    if (!this.TASK_DEFINITIONS_CONTAINER_ID) {
      throw new Error(
        "Task Definition Container ID undefined: try setting the id with set_container_id()"
      )
    }
    return this.TASK_DEFINITIONS_CONTAINER_ID
  }

  /**
   * @since 1.0.0
   * @returns {HTMLDivElement} the container for the task definitions table.
   */
  static get_task_definitions_container() {
    const container = document.getElementById(this.get_container_id())

    if (!(container instanceof HTMLDivElement)) {
      throw new Error("Could not get task definitions container.")
    }

    return container
  }

  /**
   * Draws a table of task definitions into a container.
   * @since 1.0.0
   * @param {number} event_type_id is the id of the event type containing the task definititons
   */
  static async draw_editable(event_type_id) {
    const task_definition_data = await (
      await Task_Definition_Requests.get_task_definitions(event_type_id)
    ).json()
    const container = this.get_task_definitions_container()

    const table = new Table()
    table.add_column("id", row_datum => {
      return row_datum.id
    })
    table.add_column("Title", row_datum => {
      return row_datum.title
    })
    table.add_column("Edit", () => {
      return $("<button>")
        .on("click", function(evt) {
          evt.preventDefault()
          if (!(evt.target instanceof HTMLButtonElement)) {
            throw new Error("Click event wasn't triggered from button!")
          }
          Task_Definition_Editor.open(
            Task_Definition_Table.get_task_definition_id_from_button(evt.target)
          )
        })
        .html("Edit")[0]
    })
    table.add_column("Remove", () => {
      return $("<button>")
        .on("click", function(evt) {
          Task_Definition_Table.remove_task_definition(evt)
        })
        .html("Remove")[0]
    })
    table.set_row_callback(function(row_datum, rowEl) {
      $(rowEl).attr("task_definition_id", row_datum.id)
    })
    table.draw(container, task_definition_data)
  }

  /**
   * Removes a task definition from the event type, and redraws task definition table.
   * @todo this would be better if it took as input the ids
   * @since 1.0.0
   * @param {PointerEvent} evt the button click event
   */
  static async remove_task_definition(evt) {
    evt.preventDefault()

    const event_type_id = Event_Type_Editor_Page.get_event_type_id()
    const task_definition_id = this.get_task_definition_id_from_button_click(
      evt
    )

    Task_Definition_Requests.remove_task_definition(
      event_type_id,
      task_definition_id
    )
    this.draw_editable(event_type_id)
  }
}

/**
 * This class is responsible for making requests to the task definition API
 *
 * @todo task definition data is currently typed as 'any'
 * @todo make the api namespace 'linked' to where it is defined if possible.
 * @since 1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/admin
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class Task_Definition_Requests {
  /**
   * The namespace of the plugin REST API. (the part of the uri that is after the origin, and before the api endpoints)
   * @since 1.0.0
   * @type {string}
   */
  static API_NAMESPACE = "/wp-json/caataskplugin/v1"

  /**
   * Makes a request to the API for a task definition used by an event type.
   * @since 1.0.0
   * @param {number} event_type_id is the id of the event type that contains the task definition
   * @param {number} task_definition_id is the id of the task definition
   * @returns {Promise<Response>} the response to the get request, containing the task definition data in the body.
   */
  static async get_task_definition(event_type_id, task_definition_id) {
    const response = await fetch(
      window.location.origin +
        this.API_NAMESPACE +
        `/event-types/${event_type_id}/task-definitions/${task_definition_id}`,
      {
        method: "GET"
      }
    )
    if (!response.ok) {
      throw new Error(
        `Failed to retrieve task definitions (${response.status})`
      )
    }

    return response
  }

  /**
   * Makes a request to the API for retrieving all task definitions of a given event type.
   * @since 1.0.0
   * @param {number} event_type_id is the id of the event type that contains the task definition
   * @returns {Promise<Response>} the response to the get request, containing the task definitions in the body.
   */
  static async get_task_definitions(event_type_id) {
    const response = await fetch(
      window.location.origin +
        this.API_NAMESPACE +
        `/event-types/${event_type_id}/task-definitions`,
      {
        method: "GET"
      }
    )
    if (!response.ok) {
      throw new Error(
        `Failed to retrieve task definitions (${response.status})`
      )
    }

    return response
  }

  /**
   * Makes a request to the API for removing a task definition from an event type.
   * @since 1.0.0
   * @param {number} event_type_id is the id of the event type that contains the task definition
   * @param {number} task_definition_id is the id of the task definition
   * @returns {Promise<Response>} the response to the delete request
   */
  static async remove_task_definition(event_type_id, task_definition_id) {
    const response = await fetch(
      window.location.origin +
        this.API_NAMESPACE +
        `/event-types/${event_type_id}/task-definitions/${task_definition_id}`,
      {
        method: "DELETE"
      }
    )
    if (!response.ok) {
      throw new Error(`Failed to remove task definition (${response.status})`)
    }

    return response
  }

  /**
   * Makes a request for the API to update a task definition for the event type with new data.
   * @since 1.0.0
   * @param {number} event_type_id is the id of the even type that contains the task definition
   * @param {number} task_definition_id is the id of the task definition
   * @param {any} data the new data for the task definition
   * @returns {Promise<Response>} is the response to the POST request
   */
  static async update_task_definition(event_type_id, task_definition_id, data) {
    const response = await fetch(
      window.location.origin +
        this.API_NAMESPACE +
        `/event-types/${event_type_id}/task-definitions/${task_definition_id}`,
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: data
      }
    )

    if (!response.ok) {
      throw new Error(`Failed to update task definition (${response.status})`)
    }

    return response
  }

  /**
   * Makes a request for the API to create a task definition for the event type.
   * @since 1.0.0
   * @param {number} event_type_id is the id of the event type that will contain the task definiiton
   * @param {any} data the data for the task definition
   * @returns {Promise<Response>} the response to the POST request.
   */
  static async create_task_definition(event_type_id, data) {
    const response = await fetch(
      window.location.origin +
        this.API_NAMESPACE +
        `/event-types/${event_type_id}/task-definitions`,
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: data
      }
    )

    if (!response.ok) {
      throw new Error(`Failed to update task definition (${response.status})`)
    }

    return response
  }
}

/**
 * This class is responsible for making requests to the event type API
 *
 * @todo make the api namespace 'linked' to where it is defined if possible
 * @since 1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/admin
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class Event_Type_Requests {
  /**
   * The namespace of the plugin REST API. (the part of the uri that is after the origin, and before the api endpoints)
   * @since 1.0.0
   * @type {string}
   */
  static API_NAMESPACE = "/wp-json/caataskplugin/v1"

  /**
   * Adds an event type as a subtype to a parent event type.
   * @since 1.0.0
   * @param {number} parent_event_type_id the id of the parent event type'
   * @param {number} subtype_id is the id of the event type that will be added as a subtype'
   * @returns {Promise<Response>} the response to the POST requests.
   */
  static async add_subtype(parent_event_type_id, subtype_id) {
    const response = await fetch(
      window.location.origin +
        this.API_NAMESPACE +
        `/event-types/${parent_event_type_id}/subtypes`,
      {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ subtype_id: subtype_id })
      }
    )
    if (!response.ok) {
      throw new Error("Failed to add subtype")
    }
    return response
  }

  /**
   * Removes an event type from being a subtype to the parent event type.
   * @since 1.0.0
   * @param {number} parent_event_type_id is the id of the parent event type
   * @param {number} subtype_id is the id of the event type being removed from the parent even type
   * @returns {Promise<Response>} the response to the DELETE request
   */
  static async remove_subtype(parent_event_type_id, subtype_id) {
    const response = await fetch(
      window.location.origin +
        this.API_NAMESPACE +
        `/event-types/${parent_event_type_id}/subtypes/${subtype_id}`,
      {
        method: "DELETE"
      }
    )
    if (!response.ok) {
      throw new Error("Failed to remove subtype")
    }
    return response
  }

  /**
   * Gets all of the public event types.
   * @since 1.0.0
   * @returns {Promise<Response>} the response to the GET request, containing all the event types in the body.
   */
  static async get_event_types() {
    const response = await fetch(
      window.location.origin + this.API_NAMESPACE + "event-types",
      {
        method: "GET",
        headers: {}
      }
    )
    if (!response.ok) {
      throw new Error("Failed to get event types")
    }
    return response
  }

  /**
   * Gets all of the event types that are subtypes of the parent event type.
   * @since 1.0.0
   * @param {number} parent_event_type_id the id of the parent event type
   * @returns {Promise<Response>} The response to the GET request, containing all of the subtypes in the body.
   */
  static async get_subtypes(parent_event_type_id) {
    const response = await fetch(
      window.location.origin +
        this.API_NAMESPACE +
        `/event-types/${parent_event_type_id}/subtypes?addable=true&removable=true`,
      {
        method: "GET",
        headers: {}
      }
    )
    if (!response.ok) {
      throw new Error("Failed to get subtypes")
    }
    return response
  }
}

/**
 * This class is responsible for drawing the editor that can remove and add subtypes. It draws buttons
 * and handles their click events.
 *
 * The editor that can remove and add subtypes consists of a table of event types that can be added
 * as subtypes, and a table of subtypes that can be removed.
 *
 * @todo subtype data is currently typed as 'any'
 * @package    CAA_Task_Plugin
 * @since 1.0.0
 * @subpackage CAA_Task_Plugin/admin
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class Subtype_Editor {
  /**
   * Draws the editor.
   * @since 1.0.0
   * @param {number} event_type_id the id of the event type whose subtypes are being edited.
   */
  static async draw(event_type_id) {
    const event_type_data = await (
      await Event_Type_Requests.get_subtypes(event_type_id)
    ).json()
    this.draw_addable_subtype_table(event_type_data.addable)
    this.draw_removable_subtype_table(event_type_data.removable)
  }

  /**
   * Draws a table of event types that can be added as subtypes to the event type.
   * @since 1.0.0
   * @param {any[]} addable_subtypes is an array of the event types that can be added as subtypes.
   */
  static async draw_addable_subtype_table(addable_subtypes) {
    const table = new Table()
    table.add_column("Name", function(row_datum) {
      return row_datum.display_name
    })
    table.add_column("Description", function(row_datum) {
      return row_datum.description
    })
    table.add_column("Add", function(row_datum) {
      return $("<button>")
        .attr("type", "button")
        .on("click", function(evt) {
          evt.preventDefault()
          if (!(evt.target instanceof HTMLButtonElement)) {
            throw new Error("Pointer Event wasn't triggered by button press!")
          }
          Subtype_Editor.add_event_type(
            Subtype_Editor.get_subtype_id_from_button(evt.target)
          )
        })
        .html("Add")[0]
    })
    table.set_row_callback(function(row_datum, rowEl) {
      $(rowEl).attr("event_type_id", row_datum.id)
    })
    table.draw(this.get_addable_subtypes_container(), addable_subtypes)
  }

  /**
   * Adds an event type as a subtype to the event type being edited.
   * This function can only be executed on the event type editor page.
   *
   * @since 1.0.0
   * @param {number} subtype_id is the id of the event type that should be added as a subtype to
   * the event type being edited.
   */
  static async add_event_type(subtype_id) {
    const event_type_id = Event_Type_Editor_Page.get_event_type_id()
    const res = await Event_Type_Requests.add_subtype(event_type_id, subtype_id)

    if (res.ok) {
      const event_type_data = await (
        await Event_Type_Requests.get_subtypes(event_type_id)
      ).json()
      this.draw_addable_subtype_table(event_type_data.addable)
      this.draw_removable_subtype_table(event_type_data.removable)
    }
  }

  /**
   * Draws a table of event types that can be removed as subtypes to the event type. (the existing subtypes)
   * @since 1.0.0
   * @param {any[]} removable_subtypes  is an array of the subtypes that can be removed (the existing subtypes)
   */
  static async draw_removable_subtype_table(removable_subtypes) {
    const table = new Table()
    table.add_column("Name", function(row_datum) {
      return row_datum.display_name
    })
    table.add_column("Description", function(row_datum) {
      return row_datum.description
    })
    table.add_column("Remove", function(row_datum) {
      return $("<button>")
        .attr("type", "button")
        .on("click", function(evt) {
          evt.preventDefault()
          if (!(evt.target instanceof HTMLButtonElement)) {
            throw new Error("Pointer Event wasn't triggered by button press! ")
          }
          Subtype_Editor.remove_event_type(
            Subtype_Editor.get_subtype_id_from_button(evt.target)
          )
        })
        .html("Remove")[0]
    })
    table.set_row_callback(function(row_datum, rowEl) {
      $(rowEl).attr("event_type_id", row_datum.id)
    })
    table.draw(this.get_removable_subtypes_container(), removable_subtypes)
  }

  /**
   * Removes an event type from being a subtype of the event type being edited.
   * This function can only be executed on the event type editor page.
   *
   * @since 1.0.0
   * @param {number} subtype_id is the id of the subtype that should be removed from the event type being edited.
   */
  static async remove_event_type(subtype_id) {
    const event_type_id = Event_Type_Editor_Page.get_event_type_id()
    const res = await Event_Type_Requests.remove_subtype(
      event_type_id,
      subtype_id
    )

    if (res.ok) {
      const event_type_data = await (
        await Event_Type_Requests.get_subtypes(event_type_id)
      ).json()
      this.draw_addable_subtype_table(event_type_data.addable)
      this.draw_removable_subtype_table(event_type_data.removable)
    }
  }

  /**
   * Sets the container ids for the addable subtypes table and the removable subtypes table.
   * This must be called before the table is drawn.
   *
   * @since 1.0.0
   * @param {string} addable_subtypes_container_id is the id of the container where the addable subtype table should be drawn
   * @param {string} removable_subtypes_container_id is the id of the container where the removable subtype table should be drawn
   */
  static set_container_ids(
    addable_subtypes_container_id,
    removable_subtypes_container_id
  ) {
    this.addable_subtypes_container_id = addable_subtypes_container_id
    this.removable_subtypes_container_id = removable_subtypes_container_id
  }

  /**
   * This is a helper function for getting the addable and removable subtype containers.
   * @since 1.0.0
   * @see get_addable_subtypes_container
   * @see get_removable_subtypes_container
   * @param {string} [container_id] is the id of the container. Throws error if undefined.
   * @returns {HTMLDivElement} the container element
   */
  static get_container(container_id) {
    if (!container_id) {
      throw new Error(
        "The Container ID was never set, please use 'set_container_ids' ."
      )
    }
    const container = document.getElementById(container_id)
    if (!container) {
      throw new Error("The Container ID does not match any existing elements.")
    } else if (!(container instanceof HTMLDivElement)) {
      throw new Error(
        "The matching element to the container id is not a div element."
      )
    }
    return container
  }

  /**
   * @returns {HTMLDivElement} the container that holds the addable subtypes table.
   */
  static get_addable_subtypes_container() {
    return this.get_container(this.addable_subtypes_container_id)
  }

  /**
   * @returns {HTMLDivElement} the container that holds the removable subtypes table.
   */
  static get_removable_subtypes_container() {
    return this.get_container(this.removable_subtypes_container_id)
  }

  /**
   * Gets id of button within subtypes table.
   * @since 1.0.0
   * @param {HTMLButtonElement} btn a button in one of the subtypes table.
   * @returns {number} the id of the subtype that the button was pressed for.
   */
  static get_subtype_id_from_button(btn) {
    if (
      !(btn.parentElement instanceof HTMLTableCellElement) ||
      !(btn.parentElement.parentElement instanceof HTMLTableRowElement)
    ) {
      throw new Error(
        "Failed to get subtype id: Page layout for table button is incorrect."
      )
    }

    const event_type_id_str = btn.parentElement.parentElement.getAttribute(
      "event_type_id"
    )
    if (null === event_type_id_str) {
      throw new Error(
        "Failed to get subtype id: missing attribute 'event_type_id' on table row."
      )
    }

    return Util.parseID(event_type_id_str)
  }
}

/**
 * This class is responsible for drawing the event type editor page.
 *
 * This page allows the client to modify an event type's task definitions and subtypes through a
 * collection of tables.
 *
 * @since 1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/admin
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class Event_Type_Editor_Page {
  /**
   * Draws the Subtype Editor and the Task Definition Table.
   * @since 1.0.0
   */
  static async draw() {
    const event_type_id = this.get_event_type_id()
    Subtype_Editor.draw(event_type_id)
    Task_Definition_Table.draw_editable(event_type_id)
  }

  /**
   * Sets the container ids for the editor tables.
   * This must be called before calling draw.
   *
   * @param {string} addable_subtypes_container_id is the id for the container for the table that
   * shows the event types you can add as subtypes.
   * @param {string} removable_subtypes_container_id is the the id for the container of the table
   * that shows event types that are currently subtypes.
   * @param {string} task_definitions_container_id is the id for the container of the table that
   * shows task definitions.
   */
  static set_container_ids(
    addable_subtypes_container_id,
    removable_subtypes_container_id,
    task_definitions_container_id
  ) {
    Subtype_Editor.set_container_ids(
      addable_subtypes_container_id,
      removable_subtypes_container_id
    )
    Task_Definition_Table.set_container_id(task_definitions_container_id)
  }

  /**
   * Gets the event type id of the event type that is currently being edited. (Gets this from window location)
   *
   * @since 1.0.0
   * @returns {number} The event type id
   */
  static get_event_type_id() {
    let event_type_id_match = window.location.search.match(
      /(?<=event-type-id=)([^&]*)/
    )

    if (null === event_type_id_match) {
      throw new Error("Failed to get Event Type ID from page")
    }

    return Util.parseID(event_type_id_match[0])
  }
}

/**
 * A collection of functions used to create displays on the event type manager page.
 *
 * Provides functionality to draw a table of event types that can be edited or deleted.
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/admin
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class Event_Type_Manager_Page {
  /**
   * Draws the table of event types.
   * @since 1.0.0
   */
  static async draw_event_type_table() {
    const table = new Table()
    table.add_column("ID", function(row_datum) {
      return row_datum.id
    })
    table.add_column("Name", function(row_datum) {
      return row_datum.display_name
    })
    table.add_column("Description", function(row_datum) {
      return row_datum.description
    })
    table.add_column("Edit", function(row_datum) {
      return $("<a>")
        .attr(
          "href",
          window.location.href + "&action=edit&event-type-id=" + row_datum.id
        )
        .html("Edit")[0]
    })

    const event_type_table_container = this.get_event_type_container()
    const event_types_data = await (
      await Event_Type_Requests.get_event_types()
    ).json()
    table.draw(event_type_table_container, event_types_data)
  }

  /**
   * Sets the container that the table will be drawn in to the container that has the id event_type_container_id.
   * This must be called before calling draw_event_type_table.
   *
   * @since 1.0.0
   * @param {string} event_type_container_id is the id of the container where the table of event types
   * will be drawn.
   */
  static set_event_type_container_id(event_type_container_id) {
    this.event_type_container_id = event_type_container_id
  }

  /**
   * Gets the container for the table of event types.
   * @returns {HTMLDivElement} the container for the table of the event types.
   */
  static get_event_type_container() {
    if (!this.event_type_container_id) {
      throw new Error(
        "The Event Type Container ID was never set, please use 'set_event_type_container_id' ."
      )
    }
    const container = document.getElementById(this.event_type_container_id)
    if (null === container) {
      throw new Error(
        "The Event Type Container ID does not match any existing elements."
      )
    } else if (!(container instanceof HTMLDivElement)) {
      throw new Error(
        "The matching element to the event type container id is not a div element."
      )
    }
    return container
  }
}
