<?php

namespace DialInno\Jaal;

trait SerializeModel
{
    /**
     * Get the model data.
     *
     * While this will work for most models, you can get more customization and
     * better performance by writing a custom method.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $data_object = [];

        //set meta attributes
        //todo: check for composite keys?
        $data_object['id'] = strval($this->getAttribute($this->getKeyName()));
        $data_object['type'] = isset($this->jsonApiType) ?
            $this->jsonApiType :
            snake_case((new \ReflectionClass($this))->getShortName());

        //add attributes
        //construct a blacklist
        $blacklist = $this->getHidden();

        if(is_array($this->getKeyName()))
            $blacklist = array_merge($blacklist, $this->getKeyName());
        else
            $blacklist[] = $this->getKeyName();

        //filter based on that blacklist
        $data_object['attributes'] = array_filter($this->getAttributes(), function ($key) use ($blacklist) {
            return !in_array($key, $blacklist);
        }, ARRAY_FILTER_USE_KEY);

        return $data_object;
    }
}
