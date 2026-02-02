<?php
  /** @noinspection DuplicatedCode */
  /** @noinspection SpellCheckingInspection */
  /** @noinspection NotOptimalIfConditionsInspection */
  
  use Eisodos\Abstracts\Singleton;
  use TholosEditor\TholosEditorApplication;
  
  /**
   * TholosEditor Bootstrap class
   *
   * This class provides bootstrapping functionality for Tholos. It registers and implements autoload function for
   * loading all Tholos components automatically.
   *
   * @package TholosEditor
   * @see TholosEditorApplication
   */
  class TholosEditor extends Singleton {
    
    /**
     * @var TholosEditorApplication Reference to TholosEditorApplication for quick component access.
     */
    public static TholosEditorApplication $app;
    
    /**
     * Tholos class prefix used by the autoloader for detecting Tholos-related class load requests
     */
    public const string THOLOSEDITOR_CLASS_PREFIX = "TholosEditor\\";
    
    /**
     * @throws Exception
     */
    public function init(array $options_): self {
      self::$app = TholosEditorApplication::getInstance();
      
      self::$app->init($options_);
      
      return $this;
    }
  }