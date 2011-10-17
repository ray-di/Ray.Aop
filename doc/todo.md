 * Injector weave 

## Injector Weave

This works with Ray.Di

### 1.Bind interceptor in module.

    class Module extends AbstractModule
    {
        protected function configure()
        {
            $this->bindInterceptor('*', 'WeekendBlock', array(new WeekendBlocker));
        }
    }

### 2.Annotate on target method.

    class RealBilling
    {
        /**
         * @WeekendBlock
         */
        public function charge()
        {
        //...
    }

### 3.Get weaved instance.

    $weavedBilling = $injector->getInstance('RealBilling');
    $weavedBilling->chargeOrder($args);
