<?php

namespace infoservio\fastsendnote\controllers\api\v1\webhooks;

use Craft;
use craft\web\Controller;

use infoservio\fastsendnote\components\fastsendnote\MailerFactory;
use infoservio\fastsendnote\FastSendNote;
use infoservio\fastsendnote\records\Mail as MailRecord;
use infoservio\fastsendnote\models\Mail;
use yii\web\NotAcceptableHttpException;


/**
 * Webhook Controller
 *
 * @author    endurant
 * @package   Mailmanager
 * @since     1.0.0
 */
class MailgunController extends Controller
{
    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['dropped', 'delivered', 'opened'];

    // Public Methods
    // =========================================================================

    /**
     * @param $action
     * @return bool
     * @throws NotAcceptableHttpException
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        Craft::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $request = Craft::$app->request;
        $expectedSignature = hash_hmac('sha256', $request->post('timestamp') . $request->post('token'), FastSendNote::$plugin->getSettings()->mailgunKey);

        if ($request->post('signature') !== $expectedSignature) {
            throw new NotAcceptableHttpException('Wrong signature');
        }

        return parent::beforeAction($action);
    }


    /**
     * @return array
     * @throws NotAcceptableHttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDropped()
    {
        $this->requirePostRequest();
        $email = $this->findEmail();
        $email->isDropped = 1;
        $email->save();

        return ['message' => 'Email dropped. Thanks!'];
    }

    /**
     * @return array
     * @throws NotAcceptableHttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDelivered()
    {
        $this->requirePostRequest();
        $email = $this->findEmail();
        $email->isDelivered = 1;
        $email->save();

        return ['message' => 'Email received. Thanks!'];
    }

    /**
     * @return array
     * @throws NotAcceptableHttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionOpened()
    {
        $this->requirePostRequest();
        $email = $this->findEmail();
        $email->isOpened = 1;
        $email->save();

        return ['message' => 'Email opened. Thanks!'];
    }

    /**
     * @return array|bool|Mail|null|\yii\db\ActiveRecord
     * @throws NotAcceptableHttpException
     */
    private function findEmail()
    {
        $post = Craft::$app->request->post();
        $mailgunId = $post['Message-Id'];

        $email = MailRecord::getByEmailIdAndMethod($mailgunId, MailerFactory::MAILGUN, true);
        if (!$email) {
            throw new NotAcceptableHttpException('Email not found');
        }

        return $email;
    }
}
