<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::OpenAI)]
#[UseCheapestModel]
#[MaxTokens(1200)]
class TicketTriager implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are a support ticket triage assistant. Return structured data only.
Do no include extra keys.
RULES
    - always include every key in the schema
    - if you cannot determine a value, use
        - summary: "" (empty string)
        - tags: []
PROMPT;
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'priority' => $schema->integer()->min(1)->max(5)->required(),
            'department' => $schema->string()->required(),
            'sentiment' => $schema->string()->required(),
            'tags' => $schema->array()->items($schema->string())->min(0)->max(6)->required(),
            'summary' => $schema->string()->required()
        ];
    }
}
