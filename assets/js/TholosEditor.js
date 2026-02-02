function showLoading(container_) {
  if (container_ !== undefined)
    container_.html('<div class="text-center"><i class="fa-regular fa-refresh fa-spin fa-lg"></i></div>');
  else $('#globalLoading').addClass('fa-spin');
}

function finishedLoading() {
  $('#globalLoading').removeClass('fa-spin').addClass('fa-regular');
}

function refreshLeftFrame() {
  showLoading($('#left_frame').find('.content'));
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=getLeftFrame',
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    success: function (data) {
      if (data.success === 'OK') {
        $('#left_frame').find('.content').html(data.html);
        loadComponentTree(true, '#component_tree', '');
      } else {

      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

function initRightFrame() {
  showLoading($('#right_frame').find('.content'));
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=initRightFrame',
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    success: function (data) {
      if (data.success === 'OK') {
        $('#right_frame').find('.content').html(data.html);
        initEditorTabs();
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

function deleteComponentType(id_, version_) {
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=deleteComponentType&p_id=' + id_ + '&p_version=' + version_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    id_: id_,
    success: function (data) {
      if (data.success === 'OK') {
        // delete tab if exists
        tabs = $("#editortabs");
        try {
          tab = tabs.getBSTabByID("tab_" + id_);
        } catch (err) {
          tab = null;
        }
        if (tab != null) {
          $('#editortabs > .active').prev('li').find('a').trigger('click');
          tab.removeBSTab();
          initEditorTabs();
        } else
          loadComponentTree(true, '#component_tree', '');
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
  return true;
}

function doUpdateComponentType(id_, name_) {
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: $('#form_' + id_).serialize(),
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    success: function (data) {
      if (data.success === 'OK') {
        loadComponentTree(true, '#component_tree', '');
        openComponentType(id_, name_);
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
  return true;
}

function doCreateComponentType(ancestor_id_, class_name_) {
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=createComponentType&p_ancestor_id=' + ancestor_id_ + '&p_class_name=' + class_name_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    success: function (data) {
      if (data.success === 'OK') {
        loadComponentTree(true, '#component_tree', '');
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
  return true;
}

function createComponentType(ancestor_id_, ancestor_class_name_) {
  bootbox.prompt({
    title: "Create new component type (" + ancestor_class_name_ + ")",
    value: "",
    callback: function (result) {
      if (result !== null) {
        doCreateComponentType(ancestor_id_, result);
      }
    }
  });
}

function loadComponentTree(full, treeid_, searchtext_) {
  showLoading();
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: {
      'action': 'loadComponentTree',
      'searchText': searchtext_
    },
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        $(treeid_).jstree("destroy");
        $(treeid_).jstree(
          {
            'core': {
              'data': JSON.parse(data.tree),
              'multiple': false,
              'animation': 0,
              'themes': {
                'icons': true,
                'variant': 'small'
              },
              'check_callback': true,
            },
            'contextmenu': {
              'items': function (node) {
                return {
                  "Edit": {
                    "label": "Edit",
                    "action": function (data) {
                      var inst = $.jstree.reference(data.reference),
                        obj = inst.get_node(data.reference);
                      openComponentType(obj.original.id, obj.original.name);
                    }
                  },
                  "Delete": {
                    "label": "Delete",
                    "action": function (data) {
                      var inst = $.jstree.reference(data.reference),
                        obj = inst.get_node(data.reference);
                      bootbox.confirm("Are you sure to delete " + obj.original.name + " component type?",
                        function (result) {
                          if (result === true) deleteComponentType(obj.original.id, obj.original.version, obj.original.name);
                        }
                      );
                    }
                  },
                  "Create inherited": {
                    "label": "Create inherited",
                    "action": function (data) {
                      var inst = $.jstree.reference(data.reference),
                        obj = inst.get_node(data.reference);
                      createComponentType(obj.original.id, obj.original.name);
                    }
                  }
                };
              }
            },
            'state': {'key': 'component_type_tree'},
            'types': {
              'default': {
                'icon': 'fa-regular  fa-cubes color-grey'
              },
              'TApplication': {
                'icon': 'fa-regular  fa-university color-green'
              },
              'TRoute': {
                'icon': 'fa-regular  fa-random color-purple'
              },
              'TAction': {
                'icon': 'fa-regular  fa-arrow-circle-right color-purple'
              },
              'TQuery': {
                'icon': 'fa-regular  fa-database color-green'
              },
              'TStoredProcedure': {
                'icon': 'fa-regular  fa-download color-blue'
              },
              'TRoleManager': {
                'icon': 'fa-regular  fa-lock color-maroon'
              },
              'TPage': {
                'icon': 'fa-regular  fa-desktop color-purple2'
              },
              'TPartial': {
                'icon': 'fa-regular  fa-bookmark-o color-purple2'
              },
              'TDBParam': {
                'icon': 'fa-regular  fa-chain color-lightblue'
              },
              'TDataParameter': {
                'icon': 'fa-regular  fa-chain color-lightblue'
              },
              'TGridParameter': {
                'icon': 'fa-regular  fa-chain color-lightblue'
              },
              'TLOVParameter': {
                'icon': 'fa-regular  fa-chain color-lightblue'
              },
              'TDBField': {
                'icon': 'fa-regular  fa-list-alt color-lightgreen'
              },
              'TContainer': {
                'icon': 'fa-regular  fa-square-o color-brown'
              },
              'TColumn': {
                'icon': 'fa-regular  fa-square-o color-brown'
              },
              'TWidget': {
                'icon': 'fa-regular  fa-square-o color-brown'
              },
              'TForm': {
                'icon': 'fa-regular  fa-external-link color-green'
              },
              'TQueryFilter': {
                'icon': 'fa-regular  fa-filter color-green3'
              },
              'TButton': {
                'icon': 'fa-regular  fa-caret-square-o-right color-red'
              },
              'TLink': {
                'icon': 'fa-regular  fa-caret-square-o-right color-red'
              },
              'TDateTimePicker': {
                'icon': 'fa-regular  fa-calendar color-control'
              },
              'TLOV': {
                'icon': 'fa-regular  fa-sort-amount-asc color-lightgreen'
              },
              'TGrid': {
                'icon': 'fa-regular  fa-table color-grid'
              },
              'TGridColumn': {
                'icon': 'fa-regular  fa-columns color-gridcolumn'
              },
              'TGridFilter': {
                'icon': 'fa-regular  fa-filter color-green3'
              },
              'TGridRowActions': {
                'icon': 'fa-regular  fa-square-o color-red'
              },
              'TTabs': {
                'icon': 'fa-regular  fa-folder-o color-brown'
              },
              'TTabPane': {
                'icon': 'fa-regular  fa-columns color-brown'
              },
              'TTemplate': {
                'icon': 'fa-regular  fa-file-code-o color-green'
              },
              'TImage': {
                'icon': 'fa-regular  fa-picture-o color-control'
              },
              'THidden': {
                'icon': 'fa-regular  fa-angle-double-right color-grey'
              },
              'TText': {
                'icon': 'fa-regular  fa-font color-control'
              },
              'TRadio': {
                'icon': 'fa-regular  fa-dot-circle-o color-control'
              },
              'TLabel': {
                'icon': 'fa-regular  fa-tag color-control'
              },
              'TEdit': {
                'icon': 'fa-regular  fa-pencil-square-o color-control'
              },
              'THTMLEdit': {
                'icon': 'fa-regular  fa-header color-control'
              },
              'TCheckbox': {
                'icon': 'fa-regular  fa-toggle-on color-control'
              },
              'TStatic': {
                'icon': 'fa-regular  fa-tags color-control'
              },
              'TWorkflow': {
                'icon': 'fa-regular  fa-forward color-red'
              },
              'TWorkflowStep': {
                'icon': 'fa-regular  fa-step-forward color-red'
              },
              'TModal': {
                'icon': 'fa-regular  fa-square color-brown'
              },
              'TMap': {
                'icon': 'fa-regular  fa-globe color-control'
              },
              'TMapSource': {
                'icon': 'fa-regular  fa-map-marker color-control'
              },
              'TWizard': {
                'icon': 'fa-regular  fa-magic color-brown'
              },
              'TWizardStep': {
                'icon': 'fa-regular  fa-toggle-right color-brown'
              },
              'TFormContainer': {
                'icon': 'fa-regular  fa-square-o color-green'
              },
              'TFileUpload': {
                'icon': 'fa-regular  fa-upload color-purple'
              },
              'TFileProcessor': {
                'icon': 'fa-regular  fa-file color-purple'
              },
              'TJSLib': {
                'icon': 'fa-regular  fa-puzzle-piece color-purple'
              },
              'TTimer': {
                'icon': 'fa-regular  fa-clock-o color-red'
              },
              'TConfirmDialog': {
                'icon': 'fa-regular  fa-question-circle color-red'
              },
              'TConfirmButton': {
                'icon': 'fa-regular  fa-caret-square-o-right color-red'
              },
              'TButtonDropdown': {
                'icon': 'fa-regular  fa-caret-square-o-down color-red'
              },
              'TDPOpen': {
                'icon': 'fa-regular  fa-plug color-green'
              },
              'TCell': {
                'icon': 'fa-regular  fa-square-o color-brown'
              },
              'TPDFPage': {
                'icon': 'fa-regular  fa-file-pdf-o color-purple2'
              },
              'TExternalDataProvider': {
                'icon': 'fa-regular  fa-external-link-square fa-rotate-180 color-green'
              },
              'TJSONDataProvider': {
                'icon': 'fa-regular  fa-file-text-o color-green'
              },
              'TDocumentTitle': {
                'icon': 'fa-regular  fa-header color-purple2'
              },
              'TIterator': {
                'icon': 'fa-regular  fa-sort-amount-asc color-green'
              },
              'TAPIParameter': {
                'icon': 'fa-regular  fa-chain color-lightblue'
              },
              'TDataProxyParameter': {
                'icon': 'fa-regular  fa-chain color-lightblue'
              },
              'TGlobalParameter': {
                'icon': 'fa-regular  fa-chain color-lightblue'
              },
              'TQueryFilterGroup': {
                'icon': 'fa-regular  fa-filter fa-border color-green3'
              },
              'TAPIPost': {
                'icon': 'fa-regular  fa-external-link-square color-blue'
              },
              'TButtonDropdownItem': {
                'icon': 'fa-regular  fa-caret-square-o-right color-red'
              },
              'TMenuItem': {
                'icon': 'fa-regular  fa-caret-square-o-down color-blue'
              },
            },
            'plugins': ['state', 'contextmenu', 'types']
          })
          .bind('dblclick.jstree', function (event) {
            var obj = $(treeid_).jstree().get_selected(true)[0];
            openComponentType(obj.original.id, obj.original.name);
            $(treeid_).jstree().open_node($(treeid_).jstree().get_selected(true), function () {
              ;
            }, true);
          })
        ;
      } else {

      }
      $('#component_tabs').find('a[href="#tab_' + treeid_.replace('#', '') + '"]').tab('show');
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

function openComponentType(id_, name_) {
  showLoading();
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=openComponentType&p_component_type_id=' + id_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    id_: id_,
    name_: name_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        tabs = $("#editortabs");
        tab = $('#editortabs a[href="#tab_' + id_ + '"]').length;
        if (tab > 0) { // mar van ilyen
          $('#editortabs a[href="#tab_' + id_ + '"]').tab('show');
          if (name_ != '') $('#editortabs a[href="#tab_' + id_ + '"]').html(name_);
          $('#tab_' + id_).html(data.html);
        } else {
          tabs.addBSTab("tab_" + id_, name_, data.html);
          initEditorTabs();
          $('#editortabs a[href="#tab_' + id_ + '"]').tab('show');
          $('#editortabs a[href="#tab_' + id_ + '"]').on('dblclick', function (e) {
            $('#editortabs > .active').prev('li').find('a').trigger('click');
            tab = tabs.getBSTabByID("tab_" + id_);
            tab.removeBSTab();
            initEditorTabs();
          });
        }
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

/* properties */

function removeProperty(component_type_id_, id_) {
  showLoading();
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=removeProperty&p_id=' + id_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    id_: id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        openComponentType(component_type_id_, '');
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

function addProperty(component_type_id_, id_) {
  showLoading();
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=addProperty&p_component_type_id=' + component_type_id_ + '&p_property_id=' + id_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    id_: id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        openComponentType(component_type_id_, '');
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

function addNewProperty(component_type_id_) {
  showLoading($('#tab_' + component_type_id_ + ' .componentTypeEditor_container'));
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=addNewProperty&p_linked_component_type_id=' + component_type_id_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        $('#tab_' + component_type_id_ + ' .componentTypeEditor_container').html(data.html);
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

function editProperty(component_type_id_, id_) {
  showLoading($('#tab_' + component_type_id_ + ' .componentTypeEditor_container'));
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=editProperty&p_linked_component_type_id=' + component_type_id_ + '&p_id=' + id_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        $('#tab_' + component_type_id_ + ' .componentTypeEditor_container').html(data.html);
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

function saveProperty(component_type_id_) {
  showLoading();
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: $('#property_form_' + component_type_id_).serialize(),
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        openComponentType(component_type_id_, '');
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

/* property defaults */

function disableProperty(component_type_id_, id_, disabled_) {
  showLoading();
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=disableProperty&p_component_type_id=' + component_type_id_ + '&p_property_id=' + id_ + '&p_disabled=' + disabled_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    id_: id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        openComponentType(component_type_id_, '');
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

function mandatoryProperty(component_type_id_, id_, mandatory_) {
  showLoading();
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=mandatoryProperty&p_component_type_id=' + component_type_id_ + '&p_property_id=' + id_ + '&p_mandatory=' + mandatory_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    id_: id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        openComponentType(component_type_id_, '');
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

function runtimeProperty(component_type_id_, id_, runtime_) {
  showLoading();
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=runtimeProperty&p_component_type_id=' + component_type_id_ + '&p_property_id=' + id_ + '&p_runtime=' + runtime_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    id_: id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        openComponentType(component_type_id_, '');
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

function nodataProperty(component_type_id_, id_, nodata_) {
  showLoading();
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=nodataProperty&p_component_type_id=' + component_type_id_ + '&p_property_id=' + id_ + '&p_nodata=' + nodata_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    id_: id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        openComponentType(component_type_id_, '');
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

function defaultValueProperty(component_type_id_, id_, default_value_) {
  bootbox.prompt({
    title: "Default value",
    value: default_value_,
    callback: function (result) {
      if (result !== null && result != default_value_) {
        defaultValueProperty_(component_type_id_, id_, result);
      }
    }
  });
}

function defaultValueProperty_(component_type_id_, id_, default_value_) {
  showLoading();
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=defaultValueProperty&p_component_type_id=' + component_type_id_ + '&p_property_id=' + id_ + '&p_default_value=' + default_value_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    id_: id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        openComponentType(component_type_id_, '');
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

/* events */

function removeEvent(component_type_id_, id_) {
  showLoading();
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=removeEvent&p_id=' + id_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    id_: id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        openComponentType(component_type_id_, '');
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

function addEvent(component_type_id_, id_) {
  showLoading();
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=addEvent&p_component_type_id=' + component_type_id_ + '&p_event_id=' + id_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    id_: id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        openComponentType(component_type_id_, '');
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

function addNewEvent(component_type_id_) {
  showLoading($('#tab_' + component_type_id_ + ' .componentTypeEditor_container'));
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=addNewEvent&p_linked_component_type_id=' + component_type_id_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        $('#tab_' + component_type_id_ + ' .componentTypeEditor_container').html(data.html);
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

function editEvent(component_type_id_, id_) {
  showLoading($('#tab_' + component_type_id_ + ' .componentTypeEditor_container'));
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=editEvent&p_linked_component_type_id=' + component_type_id_ + '&p_id=' + id_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        $('#tab_' + component_type_id_ + ' .componentTypeEditor_container').html(data.html);
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

function saveEvent(component_type_id_) {
  showLoading();
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: $('#event_form_' + component_type_id_).serialize(),
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        openComponentType(component_type_id_, '');
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

/* methods */

function removeMethod(component_type_id_, id_) {
  showLoading();
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=removeMethod&p_id=' + id_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    id_: id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        openComponentType(component_type_id_, '');
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

function addMethod(component_type_id_, id_) {
  showLoading();
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=addMethod&p_component_type_id=' + component_type_id_ + '&p_method_id=' + id_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    id_: id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        openComponentType(component_type_id_, '');
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

function addNewMethod(component_type_id_) {
  showLoading($('#tab_' + component_type_id_ + ' .componentTypeEditor_container'));
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=addNewMethod&p_linked_component_type_id=' + component_type_id_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        $('#tab_' + component_type_id_ + ' .componentTypeEditor_container').html(data.html);
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

function editMethod(component_type_id_, id_) {
  showLoading($('#tab_' + component_type_id_ + ' .componentTypeEditor_container'));
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: 'action=editMethod&p_linked_component_type_id=' + component_type_id_ + '&p_id=' + id_,
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        $('#tab_' + component_type_id_ + ' .componentTypeEditor_container').html(data.html);
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

function saveMethod(component_type_id_) {
  showLoading();
  $.ajax({
    url: __TholosEditorAppUrl,
    type: 'post',
    dataType: 'json',
    data: $('#method_form_' + component_type_id_).serialize(),
    contentType: "application/x-www-form-urlencoded;charset=UTF-8",
    component_type_id_: component_type_id_,
    success: function (data) {
      finishedLoading();
      if (data.success === 'OK') {
        openComponentType(component_type_id_, '');
      } else {
        bootbox.alert(data.errormsg);
      }
    },
    error: function (response, textStatus, errorThrown) {

    }
  });
}

/* end */

function initEditorTabs() {
  $('#editortabs').tab();
  $('#editortabs a').off('shown.bs.tab');
  $('#editortabs a').on('shown.bs.tab', function (e) {
    var tabid = $(e.target).attr("href") // activated tab
  });
  $('#editortabs').sortable();
}

$(document).ready(function () {

  $(document).ajaxComplete(function (event, request, settings) {
    if (request.getResponseHeader('X-Tholos-Redirect') && request.getResponseHeader('X-Tholos-Redirect').length > 0) {
      window.location = request.getResponseHeader('X-Tholos-Redirect');
    }
  });

  var minWidth = 200;

  $("#left_frame").resizable({
    autoHide: false,
    handles: 'e',
    minWidth: minWidth,
    resize: function (e, ui) {
      var parentWidth = ui.element.parent().width();
      var remainingSpace = parentWidth - ui.element.outerWidth();

      if (remainingSpace < minWidth) {
        ui.element.width((parentWidth - minWidth) / parentWidth * 100 + "%");
        remainingSpace = minWidth;
      }
      var divTwo = ui.element.next(),
        divTwoWidth = (remainingSpace - (divTwo.outerWidth() - divTwo.width())) / parentWidth * 100 + "%";
      divTwo.width(divTwoWidth);
    },
    stop: function (e, ui) {
      var parentWidth = ui.element.parent().width();
      ui.element.css({
        width: ui.element.width() / parentWidth * 100 + "%"
      });
    }
  });

  refreshLeftFrame();

  initRightFrame();

});