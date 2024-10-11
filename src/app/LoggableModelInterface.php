<?php

namespace App;

interface LoggableModelInterface
{
    // ToDo: move to trait?
    public function getDirtyWithOriginal();

    public function getLogData();

    public function getCreateLogMessage();

    public function getUpdateLogMessage($dirtyFields = null);

    public function getDeleteLogMessage();

    public function getLogMessageIdentifier();

    public function getScalarChangeableFields();
}
