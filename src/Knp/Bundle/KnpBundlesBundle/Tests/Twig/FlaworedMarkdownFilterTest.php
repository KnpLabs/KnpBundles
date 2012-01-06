<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Twig;

use Knp\Bundle\KnpBundlesBundle\Twig\Extension\FlaworedMarkdownTwigExtension;

class FlaworedMarkdownFilterTest extends \PHPUnit_Framework_TestCase
{
    private $standardCodeBlocks = <<<EOD
# Readme of __BUNDLE__

```
something else?
```

Code samples:

```  yaml
---
# FILE /myapp/Mapping/Entity.Role.dcm.yml
Entity\Role:
  type: entity
  table: roles
  id:
    id:
      type: integer
      generator:
        strategy: AUTO
```

- php code
- xml config

look here

``` php
<?php
include('doctrine.php');
```

and some xml

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
    <parameters>
        <parameter key="knp_bundles.finder.aggregate.class">Knp\Bundle\KnpBundlesBundle\Finder\Aggregate</parameter>
        <parameter key="knp_bundles.finder.google.class">Knp\Bundle\KnpBundlesBundle\Finder\Google</parameter>
        <parameter key="knp_bundles.finder.github.class">Knp\Bundle\KnpBundlesBundle\Finder\Github</parameter>
    </parameters>
</container>
```

And some standard code **here**

    <?php
    class XX
    {
        //
    }

EOD;

    /**
     * @test
     */
    public function shouldTransformStandardCodeBlocks()
    {
        $expected = <<<EOD
# Readme of __BUNDLE__

    something else?

Code samples:

    ---
    # FILE /myapp/Mapping/Entity.Role.dcm.yml
    Entity\Role:
      type: entity
      table: roles
      id:
        id:
          type: integer
          generator:
            strategy: AUTO

- php code
- xml config

look here

    <?php
    include('doctrine.php');

and some xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <container xmlns="http://symfony.com/schema/dic/services"
               xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
        <parameters>
            <parameter key="knp_bundles.finder.aggregate.class">Knp\Bundle\KnpBundlesBundle\Finder\Aggregate</parameter>
            <parameter key="knp_bundles.finder.google.class">Knp\Bundle\KnpBundlesBundle\Finder\Google</parameter>
            <parameter key="knp_bundles.finder.github.class">Knp\Bundle\KnpBundlesBundle\Finder\Github</parameter>
        </parameters>
    </container>

And some standard code **here**

    <?php
    class XX
    {
        //
    }

EOD;
        $filter = new FlaworedMarkdownTwigExtension;
        $this->assertEquals(
            $expected,
            $filter->githubMd2Md($this->standardCodeBlocks),
            'should create standard markdown out of github flawored markdown'
        );
    }

    /**
     * @test
     */
    public function shouldTransformCRLF()
    {
        $code = "#header\r\n\r\nstart code\r\n\r\n```\r\n<?php\r\necho 'hello';\r\n```";
        $expected = "#header\r\n\r\nstart code\r\n\r\n    <?php\r\n    echo 'hello';";

        $filter = new FlaworedMarkdownTwigExtension;
        $this->assertEquals(
            $expected,
            $filter->githubMd2Md($code),
            'should create standard markdown out of github flawored markdown'
        );
    }
}