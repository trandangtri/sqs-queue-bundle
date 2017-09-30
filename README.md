Simple AWS SQS Queue for Symfony
================================

This bundle provides an easy way to work with AWS SQS

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/966e2515-0177-4b80-8ef0-cfc9bd800a81/mini.png)](https://insight.sensiolabs.com/projects/966e2515-0177-4b80-8ef0-cfc9bd800a81)
[![Latest Stable Version](https://poser.pugx.org/tritran/sqs-queue-bundle/v/stable)](https://packagist.org/packages/tritran/sqs-queue-bundle)
[![Latest Unstable Version](https://poser.pugx.org/tritran/sqs-queue-bundle/v/unstable)](https://packagist.org/packages/tritran/sqs-queue-bundle)
[![Build Status](https://api.travis-ci.org/trandangtri/sqs-queue-bundle.svg?branch=master)](https://travis-ci.org/trandangtri/sqs-queue-bundle)
[![Coverage Status](https://coveralls.io/repos/github/trandangtri/sqs-queue-bundle/badge.svg?branch=master)](https://coveralls.io/github/trandangtri/sqs-queue-bundle?branch=master)

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
            new \Aws\Symfony\AwsBundle(),
            new \TriTran\SqsQueueBundle\TriTranSqsQueueBundle(),
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

aws:
    version: latest
    region: us-central-1
    credentials:
        key: not-a-real-key
        secret: "@not-a-real-secret"
```

### Step 4: Configure the Queues

Below are sample configuration for some queues in YAML format

```yml
# app/config/config.yml

tritran_sqs_queue:
    sqs_queue:
        queues:
            emailpool:
                queue_url: 'https://sqs.eu-central-1.amazonaws.com/49504XX59872/emailpool'
                worker: "@acl.service.emailpool"
                attributes:
                    receive_message_wait_time_seconds: 20
                    visibility_timeout: 30
            reminder:
                queue_url: 'https://sqs.eu-central-1.amazonaws.com/49504XX59872/reminder'
                worker: 'AclBundle\Service\Worker\ReminderWorker'
```

Full documentation of the queue options available can be read in the [Queue Attributes](http://docs.aws.amazon.com/AWSSimpleQueueService/latest/APIReference/API_SetQueueAttributes.html).

> Now, you could access to queue `emailpool` or `reminder` service via `tritran.sqs_queue.emailpool` or `tritran.sqs_queue.reminder`, it's an interface of [BaseQueue](https://github.com/trandangtri/sqs-queue-bundle/blob/master/Service/BaseQueue.php)

#### Queue Behaviours

|Behaviour|Arguments|Description|
|---|---|---|
|sendMessage|`Message` $message<br />`int` $delay = 0|Delivers a message to the specified queue.|
|receiveMessage|`int` $limit = 1|Retrieves one or more messages (up to 10), from the specified queue. Using the WaitTimeSeconds parameter enables long-poll support. For more information, see [Amazon SQS Long Polling](http://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/sqs-long-polling.html) in the Amazon SQS Developer Guide.|
|deleteMessage|`string` $receiptHandle|Deletes the specified message from the specified queue. You specify the message by using the *message's receipt handle* and not the *MessageId* you receive when you send the message. Even if the message is locked by another reader due to the visibility timeout setting, it is still deleted from the queue. If you leave a message in the queue for longer than the queue's configured retention period, Amazon SQS automatically deletes the message.|
|purge|    |Deletes the messages in a queue specified by the QueueURL parameter. **Note**: you can't retrieve a message deleted from a queue.|

#### Queue Manager Behaviours

> You could access [QueueManager](https://github.com/trandangtri/sqs-queue-bundle/blob/master/Service/QueueManager.php) via service `tritran.sqs_queue.queue_manager`

|Behaviour|Arguments|Description|
|---|---|---|
|listQueue|`string` $prefix = ''|Returns a list of your queues. The maximum number of queues that can be returned is 1,000. If you specify a value for the optional *prefix* parameter, only queues with a name that begins with the specified value are returned.|
|createQueue|`string` $queueName<br />`array` $queueAttribute|Creates a new standard or FIFO queue. You can pass one or more attributes in the request.|
|deleteQueue|`string` $queueUrl|Deletes the queue specified by the **QueueUrl**, regardless of the queue's contents. If the specified queue doesn't exist, Amazon SQS returns a successful response.|
|setQueueAttributes|`string` $queueUrl<br />`array` $queueAttribute|Sets the value of one or more queue attributes. When you change a queue's attributes, the change can take up to 60 seconds for most of the attributes to propagate throughout the Amazon SQS system|
|getQueueAttributes|`string` $queueUrl|Gets attributes for the specified queue.|

### Step 5: Setup a worker

Below are a sample implementation of a worker, which will listen to a queue to handle the messages inside.

```php
namespace AclBundle\Service\Worker;

use TriTran\SqsQueueBundle\Service\Message;
use TriTran\SqsQueueBundle\Service\Worker\AbstractWorker;

class ReminderWorker extends AbstractWorker
{
    /**
     * @param Message $message
     *
     * @return boolean
     */
    protected function execute(Message $message)
    {
        echo 'The message is: ' . $message->getBody();

        return true;
    }
}
```

And then you could make it executed as daemon in console via:

```bash
bin/console tritran:sqs_queue:worker reminder
```

> Note: **reminder** is the name of queue which you configured in the config.yml in step 4.

### Appendix: Useful Console Commands

|Behaviour|Description|
|---|---|
|tritran:sqs_queue:create|Creates a new standard or FIFO queue. You can pass one or more attributes in the request.|
|tritran:sqs_queue:update|Update queue attribute based on its configuration which shown in config.yml|
|tritran:sqs_queue:delete|Delete a queue by url and all its messages||
|tritran:sqs_queue:attr|Retrieve the attribute of a specified queue|
|tritran:sqs_queue:purge|Deletes the messages in a queue specified by the QueueURL parameter.|
|tritran:sqs_queue:worker|Start a worker that will listen to a specified SQS queue|
|tritran:sqs_queue:ping|Send a simply message to a queue, for DEBUG only|

> Note: Please using `-h` for more information for each command.
