<?php

use Symfony\Components\DependencyInjection\ContainerInterface;
use Symfony\Components\DependencyInjection\Container;
use Symfony\Components\DependencyInjection\Reference;
use Symfony\Components\DependencyInjection\Parameter;
use Symfony\Components\DependencyInjection\ParameterBag\FrozenParameterBag;

/**
 * apiDevDebugProjectContainer
 *
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @property Object $event_dispatcher
 * @property Object $error_handler
 * @property Object $http_kernel
 * @property Object $request
 * @property Object $response
 * @property Object $debug.event_dispatcher
 * @property Object $templating.debugger
 * @property Object $controller_manager
 * @property Object $controller_loader
 * @property Object $request_parser
 * @property Object $router
 * @property Object $esi
 * @property Object $esi_filter
 * @property Object $response_filter
 * @property Object $exception_handler
 * @property Object $templating.engine
 * @property Object $templating.loader.filesystem
 * @property Object $templating.loader.cache
 * @property Object $templating.loader.chain
 * @property Object $templating.helper.javascripts
 * @property Object $templating.helper.stylesheets
 * @property Object $templating.helper.slots
 * @property Object $templating.helper.assets
 * @property Object $templating.helper.request
 * @property Object $templating.helper.user
 * @property Object $templating.helper.router
 * @property Object $templating.helper.actions
 * @property Object $profiler
 * @property Object $profiler.storage
 * @property Object $profiling
 * @property Object $data_collector.config
 * @property Object $data_collector.app
 * @property Object $data_collector.timer
 * @property Object $data_collector.memory
 * @property Object $debug.toolbar
 * @property Object $zend.logger
 * @property Object $zend.logger.writer.filesystem
 * @property Object $zend.formatter.filesystem
 * @property Object $zend.logger.writer.debug
 * @property Zend\Log\Filter\Priority $zend.logger.filter
 * @property Object $templating.loader
 * @property Object $templating
 * @property Object $logger
 */
class apiDevDebugProjectContainer extends Container
{
    protected $shared = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(new FrozenParameterBag($this->getDefaultParameters()));
    }

    /**
     * Gets the 'event_dispatcher' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %debug.event_dispatcher.class% instance.
     */
    protected function getEventDispatcherService()
    {
        if (isset($this->shared['event_dispatcher'])) return $this->shared['event_dispatcher'];

        $class = 'Symfony\\Foundation\\Debug\\EventDispatcher';
        $instance = new $class($this, $this->get('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        $this->shared['event_dispatcher'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'error_handler' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %error_handler.class% instance.
     */
    protected function getErrorHandlerService()
    {
        if (isset($this->shared['error_handler'])) return $this->shared['error_handler'];

        $class = 'Symfony\\Foundation\\Debug\\ErrorHandler';
        $instance = new $class(NULL);
        $this->shared['error_handler'] = $instance;
        $instance->register();

        return $instance;
    }

    /**
     * Gets the 'http_kernel' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %http_kernel.class% instance.
     */
    protected function getHttpKernelService()
    {
        if (isset($this->shared['http_kernel'])) return $this->shared['http_kernel'];

        $class = 'Symfony\\Components\\HttpKernel\\HttpKernel';
        $instance = new $class($this->getEventDispatcherService());
        $this->shared['http_kernel'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'request' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %request.class% instance.
     */
    protected function getRequestService()
    {
        if (isset($this->shared['request'])) return $this->shared['request'];

        $class = 'Symfony\\Components\\HttpKernel\\Request';
        $instance = new $class();
        $this->shared['request'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'response' service.
     *
     * @return Object A %response.class% instance.
     */
    protected function getResponseService()
    {
        $class = 'Symfony\\Components\\HttpKernel\\Response';
        $instance = new $class();

        return $instance;
    }

    /**
     * Gets the 'debug.event_dispatcher' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %debug.event_dispatcher.class% instance.
     */
    protected function getDebug_EventDispatcherService()
    {
        if (isset($this->shared['debug.event_dispatcher'])) return $this->shared['debug.event_dispatcher'];

        $class = 'Symfony\\Foundation\\Debug\\EventDispatcher';
        $instance = new $class($this, $this->get('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        $this->shared['debug.event_dispatcher'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'templating.debugger' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %templating.debugger.class% instance.
     */
    protected function getTemplating_DebuggerService()
    {
        if (isset($this->shared['templating.debugger'])) return $this->shared['templating.debugger'];

        $class = 'Symfony\\Framework\\FoundationBundle\\Templating\\Debugger';
        $instance = new $class($this->get('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        $this->shared['templating.debugger'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'controller_manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %controller_manager.class% instance.
     */
    protected function getControllerManagerService()
    {
        if (isset($this->shared['controller_manager'])) return $this->shared['controller_manager'];

        $class = 'Symfony\\Framework\\FoundationBundle\\Controller\\ControllerManager';
        $instance = new $class($this, $this->get('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        $this->shared['controller_manager'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'controller_loader' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %controller_loader.class% instance.
     */
    protected function getControllerLoaderService()
    {
        if (isset($this->shared['controller_loader'])) return $this->shared['controller_loader'];

        $class = 'Symfony\\Framework\\FoundationBundle\\Listener\\ControllerLoader';
        $instance = new $class($this->getControllerManagerService(), $this->get('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        $this->shared['controller_loader'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'request_parser' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %request_parser.class% instance.
     */
    protected function getRequestParserService()
    {
        if (isset($this->shared['request_parser'])) return $this->shared['request_parser'];

        $class = 'Symfony\\Framework\\FoundationBundle\\Listener\\RequestParser';
        $instance = new $class($this, $this->getRouterService(), $this->get('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        $this->shared['request_parser'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'router' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %router.class% instance.
     */
    protected function getRouterService()
    {
        if (isset($this->shared['router'])) return $this->shared['router'];

        $class = 'Symfony\\Components\\Routing\\Router';
        $instance = new $class(array(0 => $this->get('kernel'), 1 => 'registerRoutes'), array('cache_dir' => '/home/thib/data/workspace/symfony2bundles/api/cache/dev', 'debug' => true, 'matcher_cache_class' => 'api'.'UrlMatcher', 'generator_cache_class' => 'api'.'UrlGenerator'));
        $this->shared['router'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'esi' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %esi.class% instance.
     */
    protected function getEsiService()
    {
        if (isset($this->shared['esi'])) return $this->shared['esi'];

        $class = 'Symfony\\Components\\HttpKernel\\Cache\\Esi';
        $instance = new $class();
        $this->shared['esi'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'esi_filter' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %esi_filter.class% instance.
     */
    protected function getEsiFilterService()
    {
        if (isset($this->shared['esi_filter'])) return $this->shared['esi_filter'];

        $class = 'Symfony\\Components\\HttpKernel\\Listener\\EsiFilter';
        $instance = new $class($this->get('esi', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        $this->shared['esi_filter'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'response_filter' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %response_filter.class% instance.
     */
    protected function getResponseFilterService()
    {
        if (isset($this->shared['response_filter'])) return $this->shared['response_filter'];

        $class = 'Symfony\\Components\\HttpKernel\\Listener\\ResponseFilter';
        $instance = new $class();
        $this->shared['response_filter'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'exception_handler' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %exception_handler.class% instance.
     */
    protected function getExceptionHandlerService()
    {
        if (isset($this->shared['exception_handler'])) return $this->shared['exception_handler'];

        $class = 'Symfony\\Framework\\FoundationBundle\\Listener\\ExceptionHandler';
        $instance = new $class($this, $this->get('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE), 'FoundationBundle:Exception:exception');
        $this->shared['exception_handler'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'templating.engine' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %templating.engine.class% instance.
     */
    protected function getTemplating_EngineService()
    {
        if (isset($this->shared['templating.engine'])) return $this->shared['templating.engine'];

        $class = 'Symfony\\Framework\\FoundationBundle\\Templating\\Engine';
        $instance = new $class($this, $this->getTemplating_Loader_FilesystemService(), array(), 'htmlspecialchars');
        $this->shared['templating.engine'] = $instance;
        $instance->setCharset('UTF-8');

        return $instance;
    }

    /**
     * Gets the 'templating.loader.filesystem' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %templating.loader.filesystem.class% instance.
     */
    protected function getTemplating_Loader_FilesystemService()
    {
        if (isset($this->shared['templating.loader.filesystem'])) return $this->shared['templating.loader.filesystem'];

        $class = 'Symfony\\Components\\Templating\\Loader\\FilesystemLoader';
        $instance = new $class(array(0 => '/home/thib/data/workspace/symfony2bundles/api/views/%bundle%/%controller%/%name%%format%.%renderer%', 1 => '/home/thib/data/workspace/symfony2bundles/api/../src/Application/%bundle%/Resources/views/%controller%/%name%%format%.%renderer%', 2 => '/home/thib/data/workspace/symfony2bundles/api/../src/Bundle/%bundle%/Resources/views/%controller%/%name%%format%.%renderer%', 3 => '/home/thib/data/workspace/symfony2bundles/api/../src/vendor/Symfony/src/Symfony/Framework/%bundle%/Resources/views/%controller%/%name%%format%.%renderer%'));
        $this->shared['templating.loader.filesystem'] = $instance;
        if ($this->has('templating.debugger')) {
            $instance->setDebugger($this->get('templating.debugger', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        }

        return $instance;
    }

    /**
     * Gets the 'templating.loader.cache' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %templating.loader.cache.class% instance.
     */
    protected function getTemplating_Loader_CacheService()
    {
        if (isset($this->shared['templating.loader.cache'])) return $this->shared['templating.loader.cache'];

        $class = 'Symfony\\Components\\Templating\\Loader\\CacheLoader';
        $instance = new $class($this->get('templating.loader.wrapped'), NULL);
        $this->shared['templating.loader.cache'] = $instance;
        if ($this->has('templating.debugger')) {
            $instance->setDebugger($this->get('templating.debugger', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        }

        return $instance;
    }

    /**
     * Gets the 'templating.loader.chain' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %templating.loader.chain.class% instance.
     */
    protected function getTemplating_Loader_ChainService()
    {
        if (isset($this->shared['templating.loader.chain'])) return $this->shared['templating.loader.chain'];

        $class = 'Symfony\\Components\\Templating\\Loader\\ChainLoader';
        $instance = new $class();
        $this->shared['templating.loader.chain'] = $instance;
        if ($this->has('templating.debugger')) {
            $instance->setDebugger($this->get('templating.debugger', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        }

        return $instance;
    }

    /**
     * Gets the 'templating.helper.javascripts' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %templating.helper.javascripts.class% instance.
     */
    protected function getTemplating_Helper_JavascriptsService()
    {
        if (isset($this->shared['templating.helper.javascripts'])) return $this->shared['templating.helper.javascripts'];

        $class = 'Symfony\\Components\\Templating\\Helper\\JavascriptsHelper';
        $instance = new $class($this->getTemplating_Helper_AssetsService());
        $this->shared['templating.helper.javascripts'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'templating.helper.stylesheets' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %templating.helper.stylesheets.class% instance.
     */
    protected function getTemplating_Helper_StylesheetsService()
    {
        if (isset($this->shared['templating.helper.stylesheets'])) return $this->shared['templating.helper.stylesheets'];

        $class = 'Symfony\\Components\\Templating\\Helper\\StylesheetsHelper';
        $instance = new $class($this->getTemplating_Helper_AssetsService());
        $this->shared['templating.helper.stylesheets'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'templating.helper.slots' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %templating.helper.slots.class% instance.
     */
    protected function getTemplating_Helper_SlotsService()
    {
        if (isset($this->shared['templating.helper.slots'])) return $this->shared['templating.helper.slots'];

        $class = 'Symfony\\Components\\Templating\\Helper\\SlotsHelper';
        $instance = new $class();
        $this->shared['templating.helper.slots'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'templating.helper.assets' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %templating.helper.assets.class% instance.
     */
    protected function getTemplating_Helper_AssetsService()
    {
        if (isset($this->shared['templating.helper.assets'])) return $this->shared['templating.helper.assets'];

        $class = 'Symfony\\Framework\\FoundationBundle\\Templating\\Helper\\AssetsHelper';
        $instance = new $class($this->getRequestService(), '', 'SomeVersionScheme');
        $this->shared['templating.helper.assets'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'templating.helper.request' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %templating.helper.request.class% instance.
     */
    protected function getTemplating_Helper_RequestService()
    {
        if (isset($this->shared['templating.helper.request'])) return $this->shared['templating.helper.request'];

        $class = 'Symfony\\Framework\\FoundationBundle\\Templating\\Helper\\RequestHelper';
        $instance = new $class($this->getRequestService());
        $this->shared['templating.helper.request'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'templating.helper.user' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %templating.helper.user.class% instance.
     */
    protected function getTemplating_Helper_UserService()
    {
        if (isset($this->shared['templating.helper.user'])) return $this->shared['templating.helper.user'];

        $class = 'Symfony\\Framework\\FoundationBundle\\Templating\\Helper\\UserHelper';
        $instance = new $class($this->get('user'));
        $this->shared['templating.helper.user'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'templating.helper.router' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %templating.helper.router.class% instance.
     */
    protected function getTemplating_Helper_RouterService()
    {
        if (isset($this->shared['templating.helper.router'])) return $this->shared['templating.helper.router'];

        $class = 'Symfony\\Framework\\FoundationBundle\\Templating\\Helper\\RouterHelper';
        $instance = new $class($this->getRouterService());
        $this->shared['templating.helper.router'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'templating.helper.actions' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %templating.helper.actions.class% instance.
     */
    protected function getTemplating_Helper_ActionsService()
    {
        if (isset($this->shared['templating.helper.actions'])) return $this->shared['templating.helper.actions'];

        $class = 'Symfony\\Framework\\FoundationBundle\\Templating\\Helper\\ActionsHelper';
        $instance = new $class($this->getControllerManagerService());
        $this->shared['templating.helper.actions'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'profiler' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %profiler.class% instance.
     */
    protected function getProfilerService()
    {
        if (isset($this->shared['profiler'])) return $this->shared['profiler'];

        $class = 'Symfony\\Framework\\FoundationBundle\\Profiler';
        $instance = new $class($this, $this->getProfiler_StorageService(), $this->get('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        $this->shared['profiler'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'profiler.storage' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %profiler.storage.class% instance.
     */
    protected function getProfiler_StorageService()
    {
        if (isset($this->shared['profiler.storage'])) return $this->shared['profiler.storage'];

        $class = 'Symfony\\Components\\HttpKernel\\Profiler\\ProfilerStorage';
        $instance = new $class('/home/thib/data/workspace/symfony2bundles/api/cache/dev/profiler.db', NULL, 86400);
        $this->shared['profiler.storage'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'profiling' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %profiling.class% instance.
     */
    protected function getProfilingService()
    {
        if (isset($this->shared['profiling'])) return $this->shared['profiling'];

        $class = 'Symfony\\Components\\HttpKernel\\Listener\\Profiling';
        $instance = new $class($this->getProfilerService());
        $this->shared['profiling'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'data_collector.config' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %data_collector.config.class% instance.
     */
    protected function getDataCollector_ConfigService()
    {
        if (isset($this->shared['data_collector.config'])) return $this->shared['data_collector.config'];

        $class = 'Symfony\\Framework\\FoundationBundle\\DataCollector\\ConfigDataCollector';
        $instance = new $class($this);
        $this->shared['data_collector.config'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'data_collector.app' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %data_collector.app.class% instance.
     */
    protected function getDataCollector_AppService()
    {
        if (isset($this->shared['data_collector.app'])) return $this->shared['data_collector.app'];

        $class = 'Symfony\\Framework\\FoundationBundle\\DataCollector\\AppDataCollector';
        $instance = new $class($this);
        $this->shared['data_collector.app'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'data_collector.timer' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %data_collector.timer.class% instance.
     */
    protected function getDataCollector_TimerService()
    {
        if (isset($this->shared['data_collector.timer'])) return $this->shared['data_collector.timer'];

        $class = 'Symfony\\Framework\\FoundationBundle\\DataCollector\\TimerDataCollector';
        $instance = new $class($this);
        $this->shared['data_collector.timer'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'data_collector.memory' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %data_collector.memory.class% instance.
     */
    protected function getDataCollector_MemoryService()
    {
        if (isset($this->shared['data_collector.memory'])) return $this->shared['data_collector.memory'];

        $class = 'Symfony\\Components\\HttpKernel\\Profiler\\DataCollector\\MemoryDataCollector';
        $instance = new $class();
        $this->shared['data_collector.memory'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'debug.toolbar' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %debug.toolbar.class% instance.
     */
    protected function getDebug_ToolbarService()
    {
        if (isset($this->shared['debug.toolbar'])) return $this->shared['debug.toolbar'];

        $class = 'Symfony\\Components\\HttpKernel\\Listener\\WebDebugToolbar';
        $instance = new $class($this->getProfilerService());
        $this->shared['debug.toolbar'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'zend.logger' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %zend.logger.class% instance.
     */
    protected function getZend_LoggerService()
    {
        if (isset($this->shared['zend.logger'])) return $this->shared['zend.logger'];

        $class = 'Symfony\\Framework\\ZendBundle\\Logger\\Logger';
        $instance = new $class();
        $this->shared['zend.logger'] = $instance;
        $instance->addWriter($this->getZend_Logger_Writer_FilesystemService());
        $instance->addWriter($this->getZend_Logger_Writer_DebugService());

        return $instance;
    }

    /**
     * Gets the 'zend.logger.writer.filesystem' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %zend.logger.writer.filesystem.class% instance.
     */
    protected function getZend_Logger_Writer_FilesystemService()
    {
        if (isset($this->shared['zend.logger.writer.filesystem'])) return $this->shared['zend.logger.writer.filesystem'];

        $class = 'Zend\\Log\\Writer\\Stream';
        $instance = new $class('/home/thib/data/workspace/symfony2bundles/api/logs/dev.log');
        $this->shared['zend.logger.writer.filesystem'] = $instance;
        $instance->addFilter($this->getZend_Logger_FilterService());
        $instance->setFormatter($this->getZend_Formatter_FilesystemService());

        return $instance;
    }

    /**
     * Gets the 'zend.formatter.filesystem' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %zend.formatter.filesystem.class% instance.
     */
    protected function getZend_Formatter_FilesystemService()
    {
        if (isset($this->shared['zend.formatter.filesystem'])) return $this->shared['zend.formatter.filesystem'];

        $class = 'Zend\\Log\\Formatter\\Simple';
        $instance = new $class('%timestamp% %priorityName%: %message%
');
        $this->shared['zend.formatter.filesystem'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'zend.logger.writer.debug' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %zend.logger.writer.debug.class% instance.
     */
    protected function getZend_Logger_Writer_DebugService()
    {
        if (isset($this->shared['zend.logger.writer.debug'])) return $this->shared['zend.logger.writer.debug'];

        $class = 'Symfony\\Framework\\ZendBundle\\Logger\\DebugLogger';
        $instance = new $class();
        $this->shared['zend.logger.writer.debug'] = $instance;
        $instance->addFilter($this->getZend_Logger_FilterService());

        return $instance;
    }

    /**
     * Gets the 'zend.logger.filter' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Zend\Log\Filter\Priority A Zend\Log\Filter\Priority instance.
     */
    protected function getZend_Logger_FilterService()
    {
        if (isset($this->shared['zend.logger.filter'])) return $this->shared['zend.logger.filter'];

        $instance = new Zend\Log\Filter\Priority(7);
        $this->shared['zend.logger.filter'] = $instance;

        return $instance;
    }

    /**
     * Gets the templating.loader service alias.
     *
     * @return Object An instance of the templating.loader.filesystem service
     */
    protected function getTemplating_LoaderService()
    {
        return $this->getTemplating_Loader_FilesystemService();
    }

    /**
     * Gets the templating service alias.
     *
     * @return Object An instance of the templating.engine service
     */
    protected function getTemplatingService()
    {
        return $this->getTemplating_EngineService();
    }

    /**
     * Gets the logger service alias.
     *
     * @return Object An instance of the zend.logger service
     */
    protected function getLoggerService()
    {
        return $this->getZend_LoggerService();
    }

    /**
     * Returns service ids for a given annotation.
     *
     * @param string $name The annotation name
     *
     * @return array An array of annotations
     */
    public function findAnnotatedServiceIds($name)
    {
        static $annotations = array (
  'kernel.listener' => 
  array (
    'controller_loader' => 
    array (
      0 => 
      array (
      ),
    ),
    'request_parser' => 
    array (
      0 => 
      array (
      ),
    ),
    'esi_filter' => 
    array (
      0 => 
      array (
      ),
    ),
    'response_filter' => 
    array (
      0 => 
      array (
      ),
    ),
    'exception_handler' => 
    array (
      0 => 
      array (
      ),
    ),
    'profiling' => 
    array (
      0 => 
      array (
      ),
    ),
    'debug.toolbar' => 
    array (
      0 => 
      array (
      ),
    ),
  ),
  'templating.helper' => 
  array (
    'templating.helper.javascripts' => 
    array (
      0 => 
      array (
        'alias' => 'javascripts',
      ),
    ),
    'templating.helper.stylesheets' => 
    array (
      0 => 
      array (
        'alias' => 'stylesheets',
      ),
    ),
    'templating.helper.slots' => 
    array (
      0 => 
      array (
        'alias' => 'slots',
      ),
    ),
    'templating.helper.assets' => 
    array (
      0 => 
      array (
        'alias' => 'assets',
      ),
    ),
    'templating.helper.request' => 
    array (
      0 => 
      array (
        'alias' => 'request',
      ),
    ),
    'templating.helper.user' => 
    array (
      0 => 
      array (
        'alias' => 'user',
      ),
    ),
    'templating.helper.router' => 
    array (
      0 => 
      array (
        'alias' => 'router',
      ),
    ),
    'templating.helper.actions' => 
    array (
      0 => 
      array (
        'alias' => 'actions',
      ),
    ),
  ),
  'data_collector' => 
  array (
    'data_collector.config' => 
    array (
      0 => 
      array (
        'core' => true,
      ),
    ),
    'data_collector.app' => 
    array (
      0 => 
      array (
        'core' => true,
      ),
    ),
    'data_collector.timer' => 
    array (
      0 => 
      array (
        'core' => true,
      ),
    ),
    'data_collector.memory' => 
    array (
      0 => 
      array (
        'core' => true,
      ),
    ),
  ),
);

        return isset($annotations[$name]) ? $annotations[$name] : array();
    }

    /**
     * Gets the default parameters.
     *
     * @return array An array of the default parameters
     */
    protected function getDefaultParameters()
    {
        return array(
            'kernel.root_dir' => '/home/thib/data/workspace/symfony2bundles/api',
            'kernel.environment' => 'dev',
            'kernel.debug' => true,
            'kernel.name' => 'api',
            'kernel.cache_dir' => '/home/thib/data/workspace/symfony2bundles/api/cache/dev',
            'kernel.logs_dir' => '/home/thib/data/workspace/symfony2bundles/api/logs',
            'kernel.bundle_dirs' => array(
                'Application' => '/home/thib/data/workspace/symfony2bundles/api/../src/Application',
                'Bundle' => '/home/thib/data/workspace/symfony2bundles/api/../src/Bundle',
                'Symfony\\Framework' => '/home/thib/data/workspace/symfony2bundles/api/../src/vendor/Symfony/src/Symfony/Framework',
            ),
            'kernel.bundles' => array(
                0 => 'Symfony\\Foundation\\KernelBundle',
                1 => 'Symfony\\Framework\\FoundationBundle\\FoundationBundle',
                2 => 'Symfony\\Framework\\ZendBundle\\ZendBundle',
                3 => 'Application\\ApiBundle\\ApiBundle',
            ),
            'kernel.charset' => 'UTF-8',
            'templating.loader.filesystem.path' => array(
                0 => '/home/thib/data/workspace/symfony2bundles/api/views/%bundle%/%controller%/%name%%format%.%renderer%',
                1 => '/home/thib/data/workspace/symfony2bundles/api/../src/Application/%bundle%/Resources/views/%controller%/%name%%format%.%renderer%',
                2 => '/home/thib/data/workspace/symfony2bundles/api/../src/Bundle/%bundle%/Resources/views/%controller%/%name%%format%.%renderer%',
                3 => '/home/thib/data/workspace/symfony2bundles/api/../src/vendor/Symfony/src/Symfony/Framework/%bundle%/Resources/views/%controller%/%name%%format%.%renderer%',
            ),
            'event_dispatcher.class' => 'Symfony\\Foundation\\EventDispatcher',
            'http_kernel.class' => 'Symfony\\Components\\HttpKernel\\HttpKernel',
            'request.class' => 'Symfony\\Components\\HttpKernel\\Request',
            'response.class' => 'Symfony\\Components\\HttpKernel\\Response',
            'error_handler.class' => 'Symfony\\Foundation\\Debug\\ErrorHandler',
            'error_handler.level' => NULL,
            'kernel.include_core_classes' => false,
            'debug.event_dispatcher.class' => 'Symfony\\Foundation\\Debug\\EventDispatcher',
            'templating.debugger.class' => 'Symfony\\Framework\\FoundationBundle\\Templating\\Debugger',
            'kernel.compiled_classes' => array(
                0 => 'Symfony\\Components\\Routing\\Router',
                1 => 'Symfony\\Components\\Routing\\RouterInterface',
                2 => 'Symfony\\Components\\EventDispatcher\\Event',
                3 => 'Symfony\\Components\\Routing\\Matcher\\UrlMatcherInterface',
                4 => 'Symfony\\Components\\Routing\\Matcher\\UrlMatcher',
                5 => 'Symfony\\Components\\HttpKernel\\HttpKernel',
                6 => 'Symfony\\Components\\HttpKernel\\Request',
                7 => 'Symfony\\Components\\HttpKernel\\Response',
                8 => 'Symfony\\Components\\HttpKernel\\Listener\\ResponseFilter',
                9 => 'Symfony\\Components\\Templating\\Loader\\LoaderInterface',
                10 => 'Symfony\\Components\\Templating\\Loader\\Loader',
                11 => 'Symfony\\Components\\Templating\\Loader\\FilesystemLoader',
                12 => 'Symfony\\Components\\Templating\\Engine',
                13 => 'Symfony\\Components\\Templating\\Renderer\\RendererInterface',
                14 => 'Symfony\\Components\\Templating\\Renderer\\Renderer',
                15 => 'Symfony\\Components\\Templating\\Renderer\\PhpRenderer',
                16 => 'Symfony\\Components\\Templating\\Storage\\Storage',
                17 => 'Symfony\\Components\\Templating\\Storage\\FileStorage',
                18 => 'Symfony\\Framework\\FoundationBundle\\Controller',
                19 => 'Symfony\\Framework\\FoundationBundle\\Listener\\RequestParser',
                20 => 'Symfony\\Framework\\FoundationBundle\\Listener\\ControllerLoader',
                21 => 'Symfony\\Framework\\FoundationBundle\\Templating\\Engine',
            ),
            'request_parser.class' => 'Symfony\\Framework\\FoundationBundle\\Listener\\RequestParser',
            'controller_manager.class' => 'Symfony\\Framework\\FoundationBundle\\Controller\\ControllerManager',
            'controller_loader.class' => 'Symfony\\Framework\\FoundationBundle\\Listener\\ControllerLoader',
            'router.class' => 'Symfony\\Components\\Routing\\Router',
            'response_filter.class' => 'Symfony\\Components\\HttpKernel\\Listener\\ResponseFilter',
            'exception_handler.class' => 'Symfony\\Framework\\FoundationBundle\\Listener\\ExceptionHandler',
            'exception_handler.controller' => 'FoundationBundle:Exception:exception',
            'esi.class' => 'Symfony\\Components\\HttpKernel\\Cache\\Esi',
            'esi_filter.class' => 'Symfony\\Components\\HttpKernel\\Listener\\EsiFilter',
            'templating.engine.class' => 'Symfony\\Framework\\FoundationBundle\\Templating\\Engine',
            'templating.loader.filesystem.class' => 'Symfony\\Components\\Templating\\Loader\\FilesystemLoader',
            'templating.loader.cache.class' => 'Symfony\\Components\\Templating\\Loader\\CacheLoader',
            'templating.loader.chain.class' => 'Symfony\\Components\\Templating\\Loader\\ChainLoader',
            'templating.helper.javascripts.class' => 'Symfony\\Components\\Templating\\Helper\\JavascriptsHelper',
            'templating.helper.stylesheets.class' => 'Symfony\\Components\\Templating\\Helper\\StylesheetsHelper',
            'templating.helper.slots.class' => 'Symfony\\Components\\Templating\\Helper\\SlotsHelper',
            'templating.helper.assets.class' => 'Symfony\\Framework\\FoundationBundle\\Templating\\Helper\\AssetsHelper',
            'templating.helper.actions.class' => 'Symfony\\Framework\\FoundationBundle\\Templating\\Helper\\ActionsHelper',
            'templating.helper.router.class' => 'Symfony\\Framework\\FoundationBundle\\Templating\\Helper\\RouterHelper',
            'templating.helper.request.class' => 'Symfony\\Framework\\FoundationBundle\\Templating\\Helper\\RequestHelper',
            'templating.helper.user.class' => 'Symfony\\Framework\\FoundationBundle\\Templating\\Helper\\UserHelper',
            'templating.output_escaper' => 'htmlspecialchars',
            'templating.assets.version' => 'SomeVersionScheme',
            'templating.loader.cache.path' => NULL,
            'profiler.class' => 'Symfony\\Framework\\FoundationBundle\\Profiler',
            'profiler.storage.class' => 'Symfony\\Components\\HttpKernel\\Profiler\\ProfilerStorage',
            'profiler.storage.file' => '/home/thib/data/workspace/symfony2bundles/api/cache/dev/profiler.db',
            'profiler.storage.lifetime' => 86400,
            'profiling.class' => 'Symfony\\Components\\HttpKernel\\Listener\\Profiling',
            'data_collector.config.class' => 'Symfony\\Framework\\FoundationBundle\\DataCollector\\ConfigDataCollector',
            'data_collector.app.class' => 'Symfony\\Framework\\FoundationBundle\\DataCollector\\AppDataCollector',
            'data_collector.timer.class' => 'Symfony\\Framework\\FoundationBundle\\DataCollector\\TimerDataCollector',
            'data_collector.memory.class' => 'Symfony\\Components\\HttpKernel\\Profiler\\DataCollector\\MemoryDataCollector',
            'debug.toolbar.class' => 'Symfony\\Components\\HttpKernel\\Listener\\WebDebugToolbar',
            'zend.logger.class' => 'Symfony\\Framework\\ZendBundle\\Logger\\Logger',
            'zend.logger.priority' => 7,
            'zend.logger.writer.debug.class' => 'Symfony\\Framework\\ZendBundle\\Logger\\DebugLogger',
            'zend.logger.writer.filesystem.class' => 'Zend\\Log\\Writer\\Stream',
            'zend.formatter.filesystem.class' => 'Zend\\Log\\Formatter\\Simple',
            'zend.formatter.filesystem.format' => '%timestamp% %priorityName%: %message%
',
            'zend.logger.path' => '/home/thib/data/workspace/symfony2bundles/api/logs/dev.log',
        );
    }
}
