Simple SQS Queue for PHP
==================================

This bundle provides an easy way to work with AWS SQS

Installation
---

Follow 5 quick steps to setup this bundle.

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following
command to download the latest stable version of this bundle:

```bash
$ composer require tritran/sqs-queue-bundle
```

> This command requires you to have Composer installed globally

### Step 2: Enable the Bundle

Register bundles in `app/AppKernel.php`:

```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            // ...
            new TriTran\SqsQueueBundle\TriTranSqsQueueBundle(),
        ];
    }

    // ...
}
```

### Step 3: Update AWS SQS Credential

This bundle is using [AWS SDK for PHP](https://github.com/aws/aws-sdk-php-symfony). Full documentation of the configuration options available can be read in the [SDK Guide](http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/configuration.html).

Below are sample configuration for AWS Credential in YAML format

```yml
# app/config/config.yml

aws_version: latest
aws_region: eu-central-1
aws_credentials_key: CREDENTIAL-KEYS
aws_credentials_secret: CREDENTIAL-SECRET
```

### Step 4: Configure the Queues

(TBC)