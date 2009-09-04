<?php

    class tagPagesGenerator extends SitemapGenerator
    {
        public function name() {
            return 'pages';
        }

        public function generate(){

        }
    }

    abstract class DataCollection
    {
        private $m_data = array();
        public function has( $key )
        { return key_exists($key, $this->m_data); }

        public function get( $key )
        { return $this->has($key) ? $this->m_data[$key] : null; }

        public function set( $key, $val )
        { $this->m_data[$key] = $val; }

        public function del( $key )
        { unset( $this->m_data[ $key ] ); }
    }

    class SitemapParserFactory extends DataCollection
    {
        static private $m_instance = null;
        
        private function __construct()
        {
            foreach( get_declared_classes() as $decl )
            {
                if( !is_subclass_of($decl, 'SitemapParser') ) continue;

                

                $instance = new $decl();
                $declname = $instance->name();

                if( !$declname || !is_string($declname) || !strlen($declname) || $declname == 'undefined_parser' )
                    continue;

                $keys = explode( ' ', $declname );

                foreach( $keys as $k )
                {
                    $k = trim($k);
                    if( !$k  ) continue;

                    $this->set($k, $instance);
                }
            }
        }

        private function __clone(){}
        static public function instance()
        {
            if( self::$m_instance === null )
                self::$m_instance = new SitemapParserFactory();

            return self::$m_instance;
        }


    }
    
    class SitemapGeneratorFactory extends DataCollection
    {
        static private $m_instance = null;
 
        private function __construct()
        {
            foreach( get_declared_classes() as $decl )
            {
                if( !is_subclass_of($decl, 'SitemapGenerator') ) continue;

                $instance = new $decl();
                $declname = $instance->name();

                if( !$declname || !is_string($declname) || !strlen($declname) || $declname == 'undefined_parser' )
                    continue;

                $keys = explode( ' ', $declname );

                foreach( $keys as $k )
                {
                    $k = trim($k);

                    if( !$k  ) continue;

                    $this->m_data[ $k ] = $instance;
                }
            }
        }

        private function __clone(){}
        static public function instance()
        {
            if( self::$m_instance === null )
                self::$m_instance = new SitemapGeneratorFactory();

            return self::$m_instance;
        }
    }

?>