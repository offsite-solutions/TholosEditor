<?php
  /** @noinspection DuplicatedCode */
  /** @noinspection SpellCheckingInspection */
  /** @noinspection NotOptimalIfConditionsInspection */
  /** @noinspection PhpUnusedPrivateMethodInspection */
  
  namespace TholosEditor;
  
  use Eisodos\Abstracts\Singleton;
  use Eisodos\Eisodos;
  use Eisodos\Interfaces\DBConnectorInterface;
  use Eisodos\Parsers\CallbackFunctionParser;
  use Eisodos\Parsers\CallbackFunctionShortParser;
  use Eisodos\Parser\SQLParser;
  use Exception;
  use JetBrains\PhpStorm\NoReturn;
  use JsonException;
  use RuntimeException;
  use Throwable;
  
  class TholosEditorApplication extends Singleton {
    
    private array $DB_Objects;
    
    /**
     * @var string
     */
    private string $definition_schema;
    
    private DBConnectorInterface $definition_db;
    
    private string $templateFolder = 'tholoseditor/';
    
    public TholosEditorCallback $callback;
    
    /**
     * @throws Exception
     */
    protected function init(array $options_): void {
      /* setting mandatory configs */
      Eisodos::$parameterHandler->setParam("TranslateLanguageTags", "F");
      
      $this->callback = new TholosEditorCallback();
      
      Eisodos::$templateEngine->registerParser(new CallbackFunctionParser());
      Eisodos::$templateEngine->registerParser(new CallbackFunctionShortParser());
      Eisodos::$templateEngine->registerParser(new SQLParser());
      
      $this->definition_schema = Eisodos::$parameterHandler->getParam("TholosEditor.DefinitionSchema", "");
      
      $this->DB_Objects = [
        // ORACLE
        "oci8.sp.component_types_delete" => "{DefinitionSchema}def_component_types_pkg.delete_row",
        "oci8.sp.component_types_insert" => "{DefinitionSchema}def_component_types_pkg.insert_row",
        "oci8.sp.component_types_update" => "{DefinitionSchema}def_component_types_pkg.update_row",
        "oci8.sp.properties_delete" => "{DefinitionSchema}def_properties_pkg.delete_row",
        "oci8.sp.properties_insert" => "{DefinitionSchema}def_properties_pkg.insert_row",
        "oci8.sp.properties_update" => "{DefinitionSchema}def_properties_pkg.update_row",
        "oci8.sp.methods_delete" => "{DefinitionSchema}def_methods_pkg.delete_row",
        "oci8.sp.methods_insert" => "{DefinitionSchema}def_methods_pkg.insert_row",
        "oci8.sp.methods_update" => "{DefinitionSchema}def_methods_pkg.update_row",
        "oci8.sp.events_delete" => "{DefinitionSchema}def_events_pkg.delete_row",
        "oci8.sp.events_insert" => "{DefinitionSchema}def_events_pkg.insert_row",
        "oci8.sp.events_update" => "{DefinitionSchema}def_events_pkg.update_row",
        "oci8.sp.component_type_properties_delete" => "{DefinitionSchema}def_component_type_prop_pkg.delete_row",
        "oci8.sp.component_type_properties_insert" => "{DefinitionSchema}def_component_type_prop_pkg.insert_row",
        "oci8.sp.component_type_properties_update" => "{DefinitionSchema}def_component_type_prop_pkg.update_row",
        "oci8.sp.component_type_prop_defs_delete" => "{DefinitionSchema}def_component_type_propdef_pkg.delete_row",
        "oci8.sp.component_type_prop_defs_insert" => "{DefinitionSchema}def_component_type_propdef_pkg.insert_row",
        "oci8.sp.component_type_prop_defs_update" => "{DefinitionSchema}def_component_type_propdef_pkg.update_row",
        "oci8.sp.component_type_methods_delete" => "{DefinitionSchema}def_component_type_methods_pkg.delete_row",
        "oci8.sp.component_type_methods_insert" => "{DefinitionSchema}def_component_type_methods_pkg.insert_row",
        "oci8.sp.component_type_methods_update" => "{DefinitionSchema}def_component_type_methods_pkg.update_row",
        "oci8.sp.component_type_events_delete" => "{DefinitionSchema}def_component_type_events_pkg.delete_row",
        "oci8.sp.component_type_events_insert" => "{DefinitionSchema}def_component_type_events_pkg.insert_row",
        "oci8.sp.component_type_events_update" => "{DefinitionSchema}def_component_type_events_pkg.update_row",
        "oci8.table.component_types" => "{DefinitionSchema}def_component_types",
        "oci8.table.properties" => "{DefinitionSchema}def_properties",
        "oci8.table.methods" => "{DefinitionSchema}def_methods",
        "oci8.table.events" => "{DefinitionSchema}def_events",
        "oci8.table.component_type_properties" => "{DefinitionSchema}def_component_type_properties",
        "oci8.table.component_type_prop_defs" => "{DefinitionSchema}def_component_type_prop_defs",
        "oci8.table.component_type_methods" => "{DefinitionSchema}def_component_type_methods",
        "oci8.table.component_type_events" => "{DefinitionSchema}def_component_type_events",
        // PostgreSQL
        "pgsql.sp.component_types_delete" => "{DefinitionSchema}def_component_types.delete_row",
        "pgsql.sp.component_types_insert" => "{DefinitionSchema}def_component_types.insert_row",
        "pgsql.sp.component_types_update" => "{DefinitionSchema}def_component_types.update_row",
        "pgsql.sp.properties_delete" => "{DefinitionSchema}def_properties.delete_row",
        "pgsql.sp.properties_insert" => "{DefinitionSchema}def_properties.insert_row",
        "pgsql.sp.properties_update" => "{DefinitionSchema}def_properties.update_row",
        "pgsql.sp.methods_delete" => "{DefinitionSchema}def_methods.delete_row",
        "pgsql.sp.methods_insert" => "{DefinitionSchema}def_methods.insert_row",
        "pgsql.sp.methods_update" => "{DefinitionSchema}def_methods.update_row",
        "pgsql.sp.events_delete" => "{DefinitionSchema}def_events.delete_row",
        "pgsql.sp.events_insert" => "{DefinitionSchema}def_events.insert_row",
        "pgsql.sp.events_update" => "{DefinitionSchema}def_events.update_row",
        "pgsql.sp.component_type_properties_delete" => "{DefinitionSchema}def_component_type_properties.delete_row",
        "pgsql.sp.component_type_properties_insert" => "{DefinitionSchema}def_component_type_properties.insert_row",
        "pgsql.sp.component_type_properties_update" => "{DefinitionSchema}def_component_type_properties.update_row",
        "pgsql.sp.component_type_prop_defs_delete" => "{DefinitionSchema}def_component_type_prop_defs.delete_row",
        "pgsql.sp.component_type_prop_defs_insert" => "{DefinitionSchema}def_component_type_prop_defs.insert_row",
        "pgsql.sp.component_type_prop_defs_update" => "{DefinitionSchema}def_component_type_prop_defs.update_row",
        "pgsql.sp.component_type_methods_delete" => "{DefinitionSchema}def_component_type_methods.delete_row",
        "pgsql.sp.component_type_methods_insert" => "{DefinitionSchema}def_component_type_methods.insert_row",
        "pgsql.sp.component_type_methods_update" => "{DefinitionSchema}def_component_type_methods.update_row",
        "pgsql.sp.component_type_events_delete" => "{DefinitionSchema}def_component_type_events.delete_row",
        "pgsql.sp.component_type_events_insert" => "{DefinitionSchema}def_component_type_events.insert_row",
        "pgsql.sp.component_type_events_update" => "{DefinitionSchema}def_component_type_events.update_row",
        "pgsql.table.component_types" => "{DefinitionSchema}def.component_types",
        "pgsql.table.properties" => "{DefinitionSchema}def.properties",
        "pgsql.table.methods" => "{DefinitionSchema}def.methods",
        "pgsql.table.events" => "{DefinitionSchema}def.events",
        "pgsql.table.component_type_properties" => "{DefinitionSchema}def.component_type_properties",
        "pgsql.table.component_type_prop_defs" => "{DefinitionSchema}def.component_type_prop_defs",
        "pgsql.table.component_type_methods" => "{DefinitionSchema}def.component_type_methods",
        "pgsql.table.component_type_events" => "{DefinitionSchema}def.component_type_events",
      ];
    }
    
    private function getDBObject(DBConnectorInterface $DBConnector_, string $object_id_): string {
      return str_replace(
        array('{DefinitionSchema}'),
        array($this->definition_schema),
        Eisodos::$utils->safe_array_value($this->DB_Objects, $DBConnector_->DBSyntax() . '.' . $object_id_, '')
      );
    }
    
    private function SPError(array $spResponse_): void {
      if ($spResponse_["p_error_code"] != "0") {
        Eisodos::$parameterHandler->setParam('SPError', 'T');
        throw new RuntimeException("[" . $spResponse_["p_error_code"] . "] " . $spResponse_["p_error_msg"]);
      }
    }
    
    public function safeHTML(string|null $text_): string {
      if ($text_ === NULL || $text_ === '') {
        return '';
      }
      
      return str_replace(array("[", "]", "\$", "^"), array("&#91;", "&#93;", "&#36;", "&#94;"), htmlspecialchars(stripslashes($text_)));
    }
    
    /**
     * @throws Throwable
     * @throws JsonException
     */
    #[NoReturn]
    public function run(
      DBConnectorInterface $definition_db_
    ): void {
      
      $this->definition_db = $definition_db_;
      
      // connect to db
      
      $this->definition_db->connect('Database');
      
      // session initialization
      
      if (Eisodos::$parameterHandler->eq("action", "")) {
        Eisodos::$templateEngine->getTemplate($this->templateFolder . "main", array(), true);
        Eisodos::$render->finish();
        exit;
      }
      
      // simple routing
      
      $action = Eisodos::$parameterHandler->getParam('action');
      if (method_exists($this, $action)) {
        $this->$action();
      }
      
      exit;
      
    }
    
    /**
     * @throws JsonException
     */
    private function getLeftFrame(): void {
      try {
        
        $responseArray['html'] = Eisodos::$templateEngine->getTemplate($this->templateFolder . "leftframe.main",
          array(), false);
        $responseArray['success'] = 'OK';
        
      } catch (Exception $e) {
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finish();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function initRightFrame(): void {
      try {
        
        $responseArray['html'] = Eisodos::$templateEngine->getTemplate($this->templateFolder . "rightframe.main", array(), false);
        $responseArray['success'] = 'OK';
        
      } catch (Exception $e) {
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finish();
      exit;
    }
    
    /**
     * @throws Exception
     */
    #[NoReturn]
    private function loadComponentTree(): void {
      
      $component_types_ = array();
      $this->definition_db->query(RT_ALL_ROWS,
        "select ct.id, \n" .
        "       ct.class_name as text, \n" .
        "       case when ''||ct.ancestor_id is null then '#' else ''||ct.ancestor_id end as parent, \n" .
        "       ct.class_name as name, \n" .
        "       ct.version, \n" .
        "       ct.class_name as type \n" .
        "  from " . $this->getDBObject($this->definition_db, "table.component_types") . " ct \n" .
        " order by ancestor_id nulls first, class_name",
        $component_types_);
      
      $responseArray['tree'] = json_encode($component_types_, JSON_THROW_ON_ERROR);
      $responseArray['success'] = 'OK';
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finish();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function openComponentType(): void {
      try {
        // getting component type 
        $this->definition_db->query(RT_FIRST_ROW, "select id,class_name,description,ancestor_id,parent_component_type_id, \n" .
          "       version,enabled \n" .
          "  from " . $this->getDBObject($this->definition_db, "table.component_types") . " ct \n" .
          " where ct.id=" . $this->definition_db->nullStrParam("p_component_type_id", false), $back);
        $first_edit = array();
        foreach ($back as $K_ => $V_) {
          $first_edit["p_" . $K_] = $this->safeHTML($V_);
        }
        
        $responseArray['html'] = Eisodos::$templateEngine->getTemplate($this->templateFolder . "rightframe.componenttype", $first_edit, false);
        $responseArray['success'] = 'OK';
      } catch (Exception $e) {
        $responseArray['success'] = 'ERROR';
        $responseArray['errormsg'] = $e->getMessage();
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function deleteComponentType(): void {
      try {
        
        $boundVariables = array();
        $this->definition_db->bindParam($boundVariables, "p_id", "integer");
        $this->definition_db->bindParam($boundVariables, "p_version", "integer");
        $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
        $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
        $resultArray = array();
        
        $this->definition_db->startTransaction();
        $this->definition_db->executeStoredProcedure(
          $this->getDBObject($this->definition_db, "sp.component_types_delete"),
          $boundVariables,
          $resultArray,
          true
        );
        $this->SPError($resultArray);
        
        $responseArray['success'] = 'OK';
        
        $this->definition_db->commit();
        
      } catch (Exception $e) {
        if ($this->definition_db->inTransaction()) {
          $this->definition_db->rollback();
        }
        $responseArray['success'] = 'ERROR';
        $responseArray['errormsg'] = $e->getMessage();
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function createComponentType(): void {
      try {
        
        $boundVariables = array();
        $this->definition_db->bindParam($boundVariables, "p_id", "integer");
        $this->definition_db->bindParam($boundVariables, "p_class_name", "text");
        $this->definition_db->bindParam($boundVariables, "p_parent_component_type_id", "integer");
        $this->definition_db->bind($boundVariables, "p_description", "text", Eisodos::$parameterHandler->getParam("p_class_name"));
        $this->definition_db->bindParam($boundVariables, "p_ancestor_id", "integer");
        
        $this->definition_db->bind($boundVariables, "p_enabled", "text", Eisodos::$parameterHandler->getParam("p_enabled", "Y"));
        $this->definition_db->bindParam($boundVariables, "p_version", "integer");
        $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
        $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
        $resultArray = array();
        
        $this->definition_db->startTransaction();
        $this->definition_db->executeStoredProcedure(
          $this->getDBObject($this->definition_db, "sp.component_types_insert"),
          $boundVariables,
          $resultArray,
          true
        );
        
        $this->SPError($resultArray);
        
        $responseArray['success'] = 'OK';
        
        $this->definition_db->commit();
        
      } catch (Exception $e) {
        
        if ($this->definition_db->inTransaction()) {
          $this->definition_db->rollback();
        }
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function updateComponentType(): void {
      try {
        
        $boundVariables = array();
        $this->definition_db->bindParam($boundVariables, "p_id", "integer");
        $this->definition_db->bindParam($boundVariables, "p_class_name", "text");
        $this->definition_db->bindParam($boundVariables, "p_parent_component_type_id", "integer");
        $this->definition_db->bindParam($boundVariables, "p_description", "text");
        $this->definition_db->bindParam($boundVariables, "p_ancestor_id", "integer");
        
        $this->definition_db->bind($boundVariables, "p_enabled", "text", Eisodos::$parameterHandler->getParam("p_enabled", "Y"));
        $this->definition_db->bindParam($boundVariables, "p_version", "integer");
        $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
        $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
        $resultArray = array();
        
        $this->definition_db->startTransaction();
        $this->definition_db->executeStoredProcedure(
          $this->getDBObject($this->definition_db, "sp.component_types_update"),
          $boundVariables,
          $resultArray,
          true
        );
        
        $this->SPError($resultArray);
        
        $responseArray['success'] = 'OK';
        
        $this->definition_db->commit();
        
      } catch (Exception $e) {
        
        if ($this->definition_db->inTransaction()) {
          $this->definition_db->rollback();
        }
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function addProperty(): void {
      try {
        
        $boundVariables = array();
        $this->definition_db->bindParam($boundVariables, "p_id", "integer");
        $this->definition_db->bindParam($boundVariables, "p_property_id", "integer");
        $this->definition_db->bindParam($boundVariables, "p_component_type_id", "integer");
        
        $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
        $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
        $resultArray = array();
        
        $this->definition_db->startTransaction();
        $this->definition_db->executeStoredProcedure(
          $this->getDBObject($this->definition_db, "sp.component_type_properties_insert"),
          $boundVariables,
          $resultArray,
          true
        );
        
        $this->SPError($resultArray);
        
        $responseArray['success'] = 'OK';
        
        $this->definition_db->commit();
        
      } catch (Exception $e) {
        
        if ($this->definition_db->inTransaction()) {
          $this->definition_db->rollback();
        }
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function removeProperty(): void {
      try {
        
        $boundVariables = array();
        $this->definition_db->bindParam($boundVariables, "p_id", "integer");
        
        $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
        $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
        $resultArray = array();
        
        $this->definition_db->startTransaction();
        $this->definition_db->executeStoredProcedure(
          $this->getDBObject($this->definition_db, "sp.component_type_properties_delete"),
          $boundVariables,
          $resultArray,
          true
        );
        
        $this->SPError($resultArray);
        
        $responseArray['success'] = 'OK';
        
        $this->definition_db->commit();
        
      } catch (Exception $e) {
        
        if ($this->definition_db->inTransaction()) {
          $this->definition_db->rollback();
        }
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function addNewProperty(): void {
      try {
        
        $responseArray['html'] = Eisodos::$templateEngine->getTemplate($this->templateFolder . "rightframe.form.property", array(), false);
        $responseArray['success'] = 'OK';
        
      } catch (Exception $e) {
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function saveProperty(): void {
      try {
        
        $boundVariables = array();
        $this->definition_db->bindParam($boundVariables, "p_id", "integer");
        $this->definition_db->bindParam($boundVariables, "p_name", "text");
        $this->definition_db->bindParam($boundVariables, "p_type", "text");
        $this->definition_db->bindParam($boundVariables, "p_value_list", "text");
        $this->definition_db->bindParam($boundVariables, "p_description", "text");
        $this->definition_db->bindParam($boundVariables, "p_component_type_id", "integer");
        
        $this->definition_db->bind($boundVariables, "p_enabled", "text", Eisodos::$parameterHandler->getParam("p_enabled", "Y"));
        $this->definition_db->bindParam($boundVariables, "p_version", "integer");
        $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
        $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
        $resultArray = array();
        
        $this->definition_db->startTransaction();
        $this->definition_db->executeStoredProcedure(
          $this->getDBObject($this->definition_db, "sp.properties_" . (Eisodos::$parameterHandler->eq("p_id", "") ? "insert" : "update")),
          $boundVariables,
          $resultArray,
          true
        );
        
        $this->SPError($resultArray);
        
        if (Eisodos::$parameterHandler->eq("p_id", "")) { // ha uj property volt, akkor hozzalinkelni
          $boundVariables = array();
          $this->definition_db->bindParam($boundVariables, "p_id", "integer");
          $this->definition_db->bind($boundVariables, "p_property_id", "integer", $resultArray["p_id"]);
          $this->definition_db->bind($boundVariables, "p_component_type_id", "integer", Eisodos::$parameterHandler->getParam("p_linked_component_type_id"));
          
          $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
          $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
          $resultArray2 = array();
          
          $this->definition_db->startTransaction();
          $this->definition_db->executeStoredProcedure(
            $this->getDBObject($this->definition_db, "sp.component_type_properties_insert"),
            $boundVariables,
            $resultArray2,
            true
          );
          
          $this->SPError($resultArray2);
        }
        
        $responseArray['success'] = 'OK';
        
        $this->definition_db->commit();
        
      } catch (Exception $e) {
        
        if ($this->definition_db->inTransaction()) {
          $this->definition_db->rollback();
        }
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function editProperty(): void {
      try {
        
        $back = array();
        $this->definition_db->query(RT_FIRST_ROW, "select id,name,description,type,component_type_id,value_list,version \n" .
          "  from " . $this->getDBObject($this->definition_db, "table.properties") . " p where p.id=" . $this->definition_db->nullStrParam("p_id"), $back);
        $first_edit = array();
        foreach ($back as $K_ => $V_) {
          $first_edit["p_" . $K_] = $this->safeHTML($V_);
        }
        
        $responseArray['html'] = Eisodos::$templateEngine->getTemplate($this->templateFolder . "rightframe.form.property", $first_edit, false);
        $responseArray['success'] = 'OK';
        
      } catch (Exception $e) {
        
        if ($this->definition_db->inTransaction()) {
          $this->definition_db->rollback();
        }
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function disableProperty(): void {
      try {
        $back = array();
        $this->definition_db->query(RT_FIRST_ROW, "select id,component_type_id,property_id,default_value,mandatory,disabled,runtime,nodata \n" .
          "  from " . $this->getDBObject($this->definition_db, "table.component_type_prop_defs") . " \n" .
          " where component_type_id=" . $this->definition_db->nullStrParam("p_component_type_id", false) . " \n" .
          "       and property_id=" . $this->definition_db->nullStrParam("p_property_id", false), $back);
        
        $boundVariables = [];
        $this->definition_db->bind($boundVariables, "p_id", "integer", $back["id"]);
        $this->definition_db->bindParam($boundVariables, "p_component_type_id", "integer");
        $this->definition_db->bindParam($boundVariables, "p_property_id", "integer");
        $this->definition_db->bind($boundVariables, "p_default_value", "text", $back["default_value"]);
        $this->definition_db->bind($boundVariables, "p_mandatory", "text", Eisodos::$parameterHandler->eq("p_disabled", "Y") ? "N" : ($back["mandatory"] == "" ? "N" : $back["mandatory"]));
        $this->definition_db->bind($boundVariables, "p_runtime", "text", Eisodos::$parameterHandler->eq("p_disabled", "Y") ? "N" : ($back["runtime"] == "" ? "N" : $back["runtime"]));
        $this->definition_db->bind($boundVariables, "p_nodata", "text", ($back["nodata"] == "" ? "N" : $back["nodata"]));
        $this->definition_db->bindParam($boundVariables, "p_disabled", "text");
        
        $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
        $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
        $resultArray = array();
        
        $this->definition_db->startTransaction();
        $this->definition_db->executeStoredProcedure(
          $this->getDBObject($this->definition_db, "sp.component_type_prop_defs_" . ($back["id"] == "" ? "insert" : "update")),
          $boundVariables,
          $resultArray,
          true
        );
        
        $this->SPError($resultArray);
        
        $responseArray['success'] = 'OK';
        
        $this->definition_db->commit();
        
      } catch (Exception $e) {
        
        if ($this->definition_db->inTransaction()) {
          $this->definition_db->rollback();
        }
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function mandatoryProperty(): void {
      try {
        $back = array();
        $this->definition_db->query(RT_FIRST_ROW, "select id,component_type_id,property_id,default_value,mandatory,disabled,runtime,nodata \n" .
          "  from " . $this->getDBObject($this->definition_db, "table.component_type_prop_defs") . " \n" .
          " where component_type_id=" . $this->definition_db->nullStrParam("p_component_type_id", false) . " \n" .
          "       and property_id=" . $this->definition_db->nullStrParam("p_property_id", false), $back);
        
        $boundVariables = [];
        $this->definition_db->bind($boundVariables, "p_id", "integer", $back["id"]);
        $this->definition_db->bindParam($boundVariables, "p_component_type_id", "integer");
        $this->definition_db->bindParam($boundVariables, "p_property_id", "integer");
        $this->definition_db->bind($boundVariables, "p_default_value", "text", $back["default_value"]);
        $this->definition_db->bind($boundVariables, "p_disabled", "text", Eisodos::$parameterHandler->eq("p_mandatory", "Y") ? "N" : ($back["disabled"] == "" ? "N" : $back["disabled"]));
        $this->definition_db->bind($boundVariables, "p_runtime", "text", Eisodos::$parameterHandler->eq("p_mandatory", "Y") ? "N" : ($back["runtime"] == "" ? "N" : $back["runtime"]));
        $this->definition_db->bind($boundVariables, "p_nodata", "text", ($back["nodata"] == "" ? "N" : $back["nodata"]));
        $this->definition_db->bindParam($boundVariables, "p_mandatory", "text");
        
        $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
        $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
        $resultArray = array();
        
        $this->definition_db->startTransaction();
        $this->definition_db->executeStoredProcedure(
          $this->getDBObject($this->definition_db, "sp.component_type_prop_defs_" . ($back["id"] == "" ? "insert" : "update")),
          $boundVariables,
          $resultArray,
          true
        );
        
        $this->SPError($resultArray);
        
        $responseArray['success'] = 'OK';
        
        $this->definition_db->commit();
        
      } catch (Exception $e) {
        
        if ($this->definition_db->inTransaction()) {
          $this->definition_db->rollback();
        }
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function runtimeProperty(): void {
      try {
        $back = array();
        $this->definition_db->query(RT_FIRST_ROW, "select id,component_type_id,property_id,default_value,mandatory,disabled,runtime,nodata \n" .
          "  from " . $this->getDBObject($this->definition_db, "table.component_type_prop_defs") . " \n" .
          " where component_type_id=" . $this->definition_db->nullStrParam("p_component_type_id", false) . " \n" .
          "       and property_id=" . $this->definition_db->nullStrParam("p_property_id", false), $back);
        
        $boundVariables = [];
        $this->definition_db->bind($boundVariables, "p_id", "integer", $back["id"]);
        $this->definition_db->bindParam($boundVariables, "p_component_type_id", "integer");
        $this->definition_db->bindParam($boundVariables, "p_property_id", "integer");
        $this->definition_db->bind($boundVariables, "p_default_value", "text", $back["default_value"]);
        $this->definition_db->bind($boundVariables, "p_disabled", "text", Eisodos::$parameterHandler->eq("p_runtime", "Y") ? "N" : ($back["disabled"] == "" ? "N" : $back["disabled"]));
        $this->definition_db->bind($boundVariables, "p_mandatory", "text", Eisodos::$parameterHandler->eq("p_runtime", "Y") ? "N" : ($back["mandatory"] == "" ? "N" : $back["mandatory"]));
        $this->definition_db->bind($boundVariables, "p_nodata", "text", ($back["nodata"] == "" ? "N" : $back["nodata"]));
        $this->definition_db->bindParam($boundVariables, "p_runtime", "text");
        
        $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
        $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
        $resultArray = array();
        
        $this->definition_db->startTransaction();
        $this->definition_db->executeStoredProcedure(
          $this->getDBObject($this->definition_db, "sp.component_type_prop_defs_" . ($back["id"] == "" ? "insert" : "update")),
          $boundVariables,
          $resultArray,
          true
        );
        
        $this->SPError($resultArray);
        
        $responseArray['success'] = 'OK';
        
        $this->definition_db->commit();
        
      } catch (Exception $e) {
        
        if ($this->definition_db->inTransaction()) {
          $this->definition_db->rollback();
        }
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function nodataProperty(): void {
      try {
        $back = array();
        $this->definition_db->query(RT_FIRST_ROW, "select id,component_type_id,property_id,default_value,mandatory,disabled,runtime,nodata \n" .
          "  from " . $this->getDBObject($this->definition_db, "table.component_type_prop_defs") . " \n" .
          " where component_type_id=" . $this->definition_db->nullStrParam("p_component_type_id", false) . " \n" .
          "       and property_id=" . $this->definition_db->nullStrParam("p_property_id", false), $back);
        
        $boundVariables = [];
        $this->definition_db->bind($boundVariables, "p_id", "integer", $back["id"]);
        $this->definition_db->bindParam($boundVariables, "p_component_type_id", "integer");
        $this->definition_db->bindParam($boundVariables, "p_property_id", "integer");
        $this->definition_db->bind($boundVariables, "p_default_value", "text", $back["default_value"]);
        $this->definition_db->bind($boundVariables, "p_disabled", "text", ($back["disabled"] == "" ? "N" : $back["disabled"]));
        $this->definition_db->bind($boundVariables, "p_mandatory", "text", ($back["mandatory"] == "" ? "N" : $back["mandatory"]));
        $this->definition_db->bind($boundVariables, "p_runtime", "text", ($back["runtime"] == "" ? "N" : $back["runtime"]));
        $this->definition_db->bindParam($boundVariables, "p_nodata", "text");
        
        $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
        $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
        $resultArray = array();
        
        $this->definition_db->startTransaction();
        $this->definition_db->executeStoredProcedure(
          $this->getDBObject($this->definition_db, "sp.component_type_prop_defs_" . ($back["id"] == "" ? "insert" : "update")),
          $boundVariables,
          $resultArray,
          true
        );
        
        $this->SPError($resultArray);
        
        $responseArray['success'] = 'OK';
        
        $this->definition_db->commit();
        
      } catch (Exception $e) {
        
        if ($this->definition_db->inTransaction()) {
          $this->definition_db->rollback();
        }
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function defaultValueProperty(): void {
      try {
        $back = array();
        $this->definition_db->query(RT_FIRST_ROW, "select id,component_type_id,property_id,default_value,mandatory,disabled,runtime,nodata \n" .
          "  from " . $this->getDBObject($this->definition_db, "table.component_type_prop_defs") . " \n" .
          " where component_type_id=" . $this->definition_db->nullStrParam("p_component_type_id", false) . " \n" .
          "       and property_id=" . $this->definition_db->nullStrParam("p_property_id", false), $back);
        
        $boundVariables = [];
        $this->definition_db->bind($boundVariables, "p_id", "integer", $back["id"]);
        $this->definition_db->bindParam($boundVariables, "p_component_type_id", "integer");
        $this->definition_db->bindParam($boundVariables, "p_property_id", "integer");
        $this->definition_db->bindParam($boundVariables, "p_default_value", "text");
        $this->definition_db->bind($boundVariables, "p_disabled", "text", ($back["disabled"] == "" ? "N" : $back["disabled"]));
        $this->definition_db->bind($boundVariables, "p_mandatory", "text", ($back["mandatory"] == "" ? "N" : $back["mandatory"]));
        $this->definition_db->bind($boundVariables, "p_runtime", "text", ($back["runtime"] == "" ? "N" : $back["runtime"]));
        $this->definition_db->bind($boundVariables, "p_nodata", "text", ($back["nodata"] == "" ? "N" : $back["nodata"]));
        
        $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
        $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
        $resultArray = array();
        
        $this->definition_db->startTransaction();
        $this->definition_db->executeStoredProcedure(
          $this->getDBObject($this->definition_db, "sp.component_type_prop_defs_" . ($back["id"] == "" ? "insert" : "update")),
          $boundVariables,
          $resultArray,
          true
        );
        
        $this->SPError($resultArray);
        
        $responseArray['success'] = 'OK';
        
        $this->definition_db->commit();
        
      } catch (Exception $e) {
        
        if ($this->definition_db->inTransaction()) {
          $this->definition_db->rollback();
        }
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /* events */
    /**
     * @throws JsonException
     */
    private function addEvent(): void {
      try {
        
        $boundVariables = array();
        $this->definition_db->bindParam($boundVariables, "p_id", "integer");
        $this->definition_db->bindParam($boundVariables, "p_event_id", "integer");
        $this->definition_db->bindParam($boundVariables, "p_component_type_id", "integer");
        
        $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
        $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
        $resultArray = array();
        
        $this->definition_db->startTransaction();
        $this->definition_db->executeStoredProcedure(
          $this->getDBObject($this->definition_db, "sp.component_type_events_insert"),
          $boundVariables,
          $resultArray,
          true
        );
        
        $this->SPError($resultArray);
        
        $responseArray['success'] = 'OK';
        
        $this->definition_db->commit();
        
      } catch (Exception $e) {
        
        if ($this->definition_db->inTransaction()) {
          $this->definition_db->rollback();
        }
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function removeEvent(): void {
      try {
        
        $boundVariables = array();
        $this->definition_db->bindParam($boundVariables, "p_id", "integer");
        
        $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
        $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
        $resultArray = array();
        
        $this->definition_db->startTransaction();
        $this->definition_db->executeStoredProcedure(
          $this->getDBObject($this->definition_db, "sp.component_type_events_delete"),
          $boundVariables,
          $resultArray,
          true
        );
        
        $this->SPError($resultArray);
        
        $responseArray['success'] = 'OK';
        
        $this->definition_db->commit();
        
      } catch (Exception $e) {
        
        if ($this->definition_db->inTransaction()) {
          $this->definition_db->rollback();
        }
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function addNewEvent(): void {
      try {
        
        $responseArray['html'] = Eisodos::$templateEngine->getTemplate($this->templateFolder . "rightframe.form.event", array(), false);
        $responseArray['success'] = 'OK';
        
      } catch (Exception $e) {
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function saveEvent(): void {
      try {
        
        $boundVariables = array();
        $this->definition_db->bindParam($boundVariables, "p_id", "integer");
        $this->definition_db->bindParam($boundVariables, "p_name", "text");
        $this->definition_db->bindParam($boundVariables, "p_type", "text");
        $this->definition_db->bindParam($boundVariables, "p_description", "text");
        
        $this->definition_db->bind($boundVariables, "p_enabled", "text", Eisodos::$parameterHandler->getParam("p_enabled", "Y"));
        $this->definition_db->bindParam($boundVariables, "p_version", "integer");
        $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
        $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
        $resultArray = array();
        
        $this->definition_db->startTransaction();
        $this->definition_db->executeStoredProcedure(
          $this->getDBObject($this->definition_db, "sp.events_" . (Eisodos::$parameterHandler->eq("p_id", "") ? "insert" : "update")),
          $boundVariables,
          $resultArray,
          true
        );
        
        $this->SPError($resultArray);
        
        if (Eisodos::$parameterHandler->eq("p_id", "")) { // ha uj property volt, akkor hozzalinkelni
          $boundVariables = array();
          $this->definition_db->bindParam($boundVariables, "p_id", "integer");
          $this->definition_db->bind($boundVariables, "p_event_id", "integer", $resultArray["p_id"]);
          $this->definition_db->bind($boundVariables, "p_component_type_id", "integer", Eisodos::$parameterHandler->getParam("p_linked_component_type_id"));
          
          $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
          $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
          $resultArray2 = array();
          
          $this->definition_db->startTransaction();
          $this->definition_db->executeStoredProcedure(
            $this->getDBObject($this->definition_db, "sp.component_type_events_insert"),
            $boundVariables,
            $resultArray2,
            true
          );
          
          $this->SPError($resultArray2);
        }
        
        $responseArray['success'] = 'OK';
        
        $this->definition_db->commit();
        
      } catch (Exception $e) {
        
        if ($this->definition_db->inTransaction()) {
          $this->definition_db->rollback();
        }
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function editEvent(): void {
      try {
        
        $back = array();
        $this->definition_db->query(RT_FIRST_ROW, "select id,name,description,type,version \n" .
          "  from " . $this->getDBObject($this->definition_db, "table.events") . " p where p.id=" . $this->definition_db->nullStrParam("p_id"), $back);
        $first_edit = array();
        foreach ($back as $K_ => $V_) {
          $first_edit["p_" . $K_] = $this->safeHTML($V_);
        }
        
        $responseArray['html'] = Eisodos::$templateEngine->getTemplate($this->templateFolder . "rightframe.form.event", $first_edit, false);
        $responseArray['success'] = 'OK';
        
      } catch (Exception $e) {
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    } /* methods */
    /**
     * @throws JsonException
     */
    private function addMethod(): void {
      try {
        
        $boundVariables = array();
        $this->definition_db->bindParam($boundVariables, "p_id", "integer");
        $this->definition_db->bindParam($boundVariables, "p_method_id", "integer");
        $this->definition_db->bindParam($boundVariables, "p_component_type_id", "integer");
        
        $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
        $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
        $resultArray = array();
        
        $this->definition_db->startTransaction();
        $this->definition_db->executeStoredProcedure(
          $this->getDBObject($this->definition_db, "sp.component_type_methods_insert"),
          $boundVariables,
          $resultArray,
          true
        );
        
        $this->SPError($resultArray);
        
        $responseArray['success'] = 'OK';
        
        $this->definition_db->commit();
        
      } catch (Exception $e) {
        
        if ($this->definition_db->inTransaction()) {
          $this->definition_db->rollback();
        }
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function removeMethod(): void {
      try {
        
        $boundVariables = array();
        $this->definition_db->bindParam($boundVariables, "p_id", "integer");
        
        $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
        $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
        $resultArray = array();
        
        $this->definition_db->startTransaction();
        $this->definition_db->executeStoredProcedure(
          $this->getDBObject($this->definition_db, "sp.component_type_methods_delete"),
          $boundVariables,
          $resultArray,
          true
        );
        
        $this->SPError($resultArray);
        
        $responseArray['success'] = 'OK';
        
        $this->definition_db->commit();
        
      } catch (Exception $e) {
        
        if ($this->definition_db->inTransaction()) {
          $this->definition_db->rollback();
        }
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function addNewMethod(): void {
      try {
        
        $responseArray['html'] = Eisodos::$templateEngine->getTemplate($this->templateFolder . "rightframe.form.method", array(), false);
        $responseArray['success'] = 'OK';
        
      } catch (Exception $e) {
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function saveMethod(): void {
      try {
        
        $boundVariables = array();
        $this->definition_db->bindParam($boundVariables, "p_id", "integer");
        $this->definition_db->bindParam($boundVariables, "p_name", "text");
        $this->definition_db->bindParam($boundVariables, "p_description", "text");
        
        $this->definition_db->bind($boundVariables, "p_enabled", "text", Eisodos::$parameterHandler->getParam("p_enabled", "Y"));
        $this->definition_db->bindParam($boundVariables, "p_version", "integer");
        $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
        $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
        $resultArray = array();
        
        $this->definition_db->startTransaction();
        $this->definition_db->executeStoredProcedure(
          $this->getDBObject($this->definition_db, "sp.methods_" . (Eisodos::$parameterHandler->eq("p_id", "") ? "insert" : "update")),
          $boundVariables,
          $resultArray,
          true
        );
        
        $this->SPError($resultArray);
        
        if (Eisodos::$parameterHandler->eq("p_id", "")) { // ha uj property volt, akkor hozzalinkelni
          $boundVariables = array();
          $this->definition_db->bindParam($boundVariables, "p_id", "integer");
          $this->definition_db->bind($boundVariables, "p_method_id", "integer", $resultArray["p_id"]);
          $this->definition_db->bind($boundVariables, "p_component_type_id", "integer", Eisodos::$parameterHandler->getParam("p_linked_component_type_id"));
          
          $this->definition_db->bind($boundVariables, "p_error_msg", "text", "");
          $this->definition_db->bind($boundVariables, "p_error_code", "integer", "");
          $resultArray2 = array();
          
          $this->definition_db->startTransaction();
          $this->definition_db->executeStoredProcedure(
            $this->getDBObject($this->definition_db, "sp.component_type_methods_insert"),
            $boundVariables,
            $resultArray2,
            true
          );
          
          $this->SPError($resultArray2);
        }
        
        $responseArray['success'] = 'OK';
        
        $this->definition_db->commit();
        
      } catch (Exception $e) {
        
        if ($this->definition_db->inTransaction()) {
          $this->definition_db->rollback();
        }
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    /**
     * @throws JsonException
     */
    private function editMethod(): void {
      try {
        
        $back = array();
        $this->definition_db->query(RT_FIRST_ROW, "select id,name,description,version \n" .
          "  from " . $this->getDBObject($this->definition_db, "table.methods") . " p where p.id=" . $this->definition_db->nullStrParam("p_id"), $back);
        $first_edit = array();
        foreach ($back as $K_ => $V_) {
          $first_edit["p_" . $K_] = $this->safeHTML($V_);
        }
        
        $responseArray['html'] = Eisodos::$templateEngine->getTemplate($this->templateFolder . "rightframe.form.method", $first_edit, false);
        $responseArray['success'] = 'OK';
        
      } catch (Exception $e) {
        
        if ($this->definition_db->inTransaction()) {
          $this->definition_db->rollback();
        }
        
        if ($e->getMessage() != "") {
          $responseArray['errormsg'] = $e->getMessage();
          if (Eisodos::$parameterHandler->neq('SPError', 'T')) {
            Eisodos::$logger->writeErrorLog($e);
          }
        }
        
        $responseArray['success'] = 'ERROR';
        
      }
      header('Content-type: application/json');
      Eisodos::$templateEngine->addToResponse(json_encode($responseArray, JSON_THROW_ON_ERROR));
      Eisodos::$render->finishRaw();
      exit;
    }
    
    
  }