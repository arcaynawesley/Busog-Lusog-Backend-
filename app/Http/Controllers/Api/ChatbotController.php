<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatbotConversation;
use App\Models\RiskPrediction;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    private const FAQ = [
        [['stunt'], "Stunting is low height-for-age from chronic undernutrition. Prevention "
            . "centers on adequate maternal nutrition, exclusive breastfeeding for the first "
            . "6 months, and timely complementary feeding from 6 months."],
        [['wast', 'muac'], "Wasting is low weight-for-height / a low MUAC, usually from acute "
            . "illness or insufficient recent food intake. It needs prompt referral and, if "
            . "severe, therapeutic feeding."],
        [['vitamin a'], "Vitamin A supplementation is typically given to children 6–59 months "
            . "every 6 months per the DOH schedule; check the child's record for their last "
            . "dose before advising a caregiver."],
        [['follow', 'schedule'], "Follow-up intervals in BusogLusog are risk-based: very high "
            . "risk → 7 days, high → 14 days, moderate → 30 days, low → 90 days."],
        [['diarrhea'], "For recent diarrheal illness, ensure oral rehydration, continued "
            . "feeding/breastfeeding, and zinc supplementation per DOH IMCI guidelines; refer "
            . "if there are danger signs (lethargy, sunken eyes, inability to drink)."],
    ];

    private const DEFAULT_REPLY = "I can help with general child-nutrition guidance (stunting, "
        . "wasting, Vitamin A, follow-up scheduling, feeding advice). For this child's specific "
        . "risk assessment, check their Risk Prediction and Recommendations tabs. This assistant "
        . "is a placeholder rule-based responder — wire in an LLM (e.g. via the Anthropic API) "
        . "here for open-ended, grounded conversation.";

    private function generateReply(string $message, ?array $childContext): string
    {
        $lower = strtolower($message);
        foreach (self::FAQ as [$keywords, $answer]) {
            foreach ($keywords as $keyword) {
                if (str_contains($lower, $keyword)) {
                    if ($childContext) {
                        $level = $childContext['risk_level'] ?? 'unassessed';
                        return "{$answer}\n\n(Context: this child's latest risk level is {$level}.)";
                    }
                    return $answer;
                }
            }
        }
        return self::DEFAULT_REPLY;
    }

    public function sendMessage(Request $request)
    {
        $data = $request->validate([
            'conversation_id' => 'nullable|uuid|exists:chatbot_conversations,id',
            'child_id' => 'nullable|uuid|exists:children,id',
            'message' => 'required|string',
        ]);

        $conversation = $data['conversation_id']
            ? ChatbotConversation::findOrFail($data['conversation_id'])
            : ChatbotConversation::create([
                'user_id' => $request->user()->id,
                'child_id' => $data['child_id'] ?? null,
                'messages' => [],
            ]);

        $childContext = null;
        if (!empty($data['child_id'])) {
            $latest = RiskPrediction::where('child_id', $data['child_id'])
                ->orderByDesc('predicted_at')
                ->first();
            if ($latest) {
                $childContext = ['risk_level' => $latest->risk_level];
            }
        }

        $reply = $this->generateReply($data['message'], $childContext);

        $messages = $conversation->messages;
        $messages[] = ['role' => 'user', 'content' => $data['message'], 'ts' => now()->toISOString()];
        $messages[] = ['role' => 'assistant', 'content' => $reply, 'ts' => now()->toISOString()];
        $conversation->update(['messages' => $messages]);

        return response()->json([
            'conversation_id' => $conversation->id,
            'reply' => $reply,
            'messages' => $messages,
        ]);
    }
}