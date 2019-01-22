<?php
declare(strict_types=1);

namespace Dynatech\Libraries\BeeQueue;


class Job
{
    /** @var int|null */
    protected $id;
    /** @var Queue */
    protected $queue;
    /** @var array */
    protected $data;
    /** @var array */
    protected $options;
    /** @var string */
    protected $status;

    /**
     * Job constructor.
     * @param Queue $queue
     * @param int|null $id
     * @param array $data
     * @param array $options
     * @throws \Exception
     */
    public function __construct(
        Queue $queue,
        ?int $id,
        array $data = [],
        array $options = []
    ) {
        $this->queue                = $queue;
        $this->id                   = $id;
        $this->data                 = $data;
        $this->options              = $options;
        $this->options['timestamp'] = $this->options['timestamp'] ?? date("Y-m-d H:i:s");
        $this->status               = 'created';
    }

    /**
     * @return Job
     * @throws \Exception
     */
    public function save(): Job
    {
        if (isset($this->options['delay'])) {
            $jobId = $this->queue->evalScript(
                'addDelayedJob',
                4,
                $this->queue->toKey('id'),
                $this->queue->toKey('jobs'),
                $this->queue->toKey('delayed'),
                $this->queue->toKey('earlierDelayed'),
                $this->id ?? '',
                $this->toData(),
                $this->options['delay']);
//
//        if (this.queue.settings.activateDelayedJobs) {
//            promise = promise.then((jobId) => {
//                // Only reschedule if the job was actually created.
//                if (jobId) {
//                    this.queue._delayedTimer.schedule(this.options.delay);
//                }
//                return jobId;
//            });
//      }
        } else {
            $jobId = $this->queue->evalScript(
                'addJob',
                3,
                $this->queue->toKey('id'),
                $this->queue->toKey('jobs'),
                $this->queue->toKey('waiting'),
                $this->id ?? '',
                $this->toData());
        }

        $this->id = (int)$jobId;
        if ($jobId && $this->queue->isStoreJobs()) {
//            this.queue.jobs.set(jobId, this);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function toData(): string
    {
        return json_encode([
            'data'    => $this->data,
            'options' => $this->options,
            'status'  => $this->status
        ]);
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

}
