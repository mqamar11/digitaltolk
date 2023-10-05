<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    //======================NO TRY CATCH is USED in All APIS============================================//

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
    try {
        $user = auth()->user();

        if ($user->user_type == env('ADMIN_ROLE_ID') || $user->user_type == env('SUPERADMIN_ROLE_ID')) {
            $response = $this->repository->getAll($request);
        } else {
            $user_id = $request->get('user_id');
            $response = $this->repository->getUsersJobs($user_id);
        }
    
        return response($response);
    } catch (\Throwable $th) {
        //throw $th;
    }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
      try {
        $job = $this->repository->with('translatorJobRel.user')->find($id);

        return response($job);
      } catch (\Throwable $th) {
        //throw $th;
      }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
       try {
        $data = $request->all(); //validations are not used. proper requests should always implement this method.

        $response = $this->repository->store(auth()->user(), $data);

        return response($response);
       } catch (\Throwable $th) {
        //throw $th;
       }

    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
      try {
        $data = $request->all(); //validations are not used. proper requests should always implement this method.
        $cuser = auth()->user();
        $response = $this->repository->updateJob($id, array_except($data, ['_token', 'submit']), $cuser);

        return response($response);
      } catch (\Throwable $th) {
        //throw $th;
      }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
   try {
         // extra line below
        // $adminSenderEmail = config('app.adminemail'); 
        $data = $request->all(); //validations are not used. proper requests should always implement this method.

        $response = $this->repository->storeJobEmail($data);

        return response($response);
   } catch (\Throwable $th) {
    //throw $th;
   }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
try {
    $user_id = $request->get('user_id');
    if($user_id) {

        $response = $this->repository->getUsersJobsHistory($user_id, $request);
        return response($response);
    }

    return response()->json(['message' => 'User ID not provided'], 400);
} catch (\Throwable $th) {
    //throw $th;
}
    }


    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
 try {
    $data = $request->all();
    $user = auth()->user();

    $response = $this->repository->acceptJob($data, $user);

    return response($response);
 } catch (\Throwable $th) {
    //throw $th;
 }
    }

    public function acceptJobWithId(Request $request)
    {
  try {
    $data = $request->get('job_id');
    $user = auth()->user();

    $response = $this->repository->acceptJobWithId($data, $user);

    return response($response);
  } catch (\Throwable $th) {
    //throw $th;
  }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
    try {
        $data = $request->all();
        $user = auth()->user();

        $response = $this->repository->cancelJobAjax($data, $user);

        return response($response);
    } catch (\Throwable $th) {
        //throw $th;
    }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
try {
    $data = $request->all();

    $response = $this->repository->endJob($data);

    return response($response);
} catch (\Throwable $th) {
    //throw $th;
}

    }

    public function customerNotCall(Request $request)
    {
    try {
        $data = $request->all();

        $response = $this->repository->customerNotCall($data);

        return response($response);
    } catch (\Throwable $th) {
        //throw $th;
    }

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
 try {
           // $data = $request->all(); extra line
           $user = auth()->user();

           $response = $this->repository->getPotentialJobs($user);
   
           return response($response);
 } catch (\Throwable $th) {
    //throw $th;
 }
    }

    public function distanceFeed(Request $request)
    {
       try {
        $data = $request->all(); //validation request not used

        if (isset($data['distance']) && $data['distance'] != "") {
            $distance = $data['distance'];
        } else {
            $distance = "";
        }
        if (isset($data['time']) && $data['time'] != "") {
            $time = $data['time'];
        } else {
            $time = "";
        }
        if (isset($data['jobid']) && $data['jobid'] != "") {
            $jobid = $data['jobid'];
        }

        if (isset($data['session_time']) && $data['session_time'] != "") {
            $session = $data['session_time'];
        } else {
            $session = "";
        }

        if ($data['flagged'] == 'true') {
            if($data['admincomment'] == '') return "Please, add comment";
            $flagged = 'yes';
        } else {
            $flagged = 'no';
        }
        
        if ($data['manually_handled'] == 'true') {
            $manually_handled = 'yes';
        } else {
            $manually_handled = 'no';
        }

        if ($data['by_admin'] == 'true') {
            $by_admin = 'yes';
        } else {
            $by_admin = 'no';
        }

        if (isset($data['admincomment']) && $data['admincomment'] != "") {
            $admincomment = $data['admincomment'];
        } else {
            $admincomment = "";
        }
        if ($time || $distance) {

            $affectedRows = Distance::where('job_id', '=', $jobid)->update(array('distance' => $distance, 'time' => $time));
        }

        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {

            $affectedRows1 = Job::where('id', '=', $jobid)->update(array('admin_comments' => $admincomment, 'flagged' => $flagged, 'session_time' => $session, 'manually_handled' => $manually_handled, 'by_admin' => $by_admin));

        }

        return response('Record updated!');
       } catch (\Throwable $th) {
        //throw $th;
       }
    }

    public function reopen(Request $request)
    {
      try {
        $data = $request->all();
        $response = $this->repository->reopen($data);

        return response($response);
      } catch (\Throwable $th) {
        //throw $th;
      }
    }

    public function resendNotifications(Request $request)
    {
      try {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $job_data, '*');

        return response(['success' => 'Push sent']);
      } catch (\Throwable $th) {
        //throw $th;
      }
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
  try {
    $data = $request->all();
    $job = $this->repository->find($data['jobid']);
    // $job_data = $this->repository->jobToData($job); extra line

    try {
        $this->repository->sendSMSNotificationToTranslator($job);
        return response(['success' => 'SMS sent']);
    } catch (\Exception $e) {
        return response(['success' => $e->getMessage()]);
    }
  } catch (\Throwable $th) {
    //throw $th;
  }
    }

}
