<script setup lang="ts">
import Layout from '../Layout.vue';
import { Form } from "@inertiajs/vue3";
import { auth } from '@/routes/user';
import Error from '@/shared/Error.vue';
</script>

<template>
    <Layout>
        <div class="flex flex-col items-center justify-center gap-4 mt-20">
            <h1>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>

            </h1>
            <Form method="post" :action="auth()">
                <div class="flex flex-col m-auto mb-4 gap-2 shrink-0 max-w-56">
                    <label for="email" class="input-label">Email:</label>
                    <input type="email" id="email" name="email" class="form-input" autofocus />
                    <label for="password" class="input-label mt-2">Password:</label>
                    <input type="password" id="password" name="password" class="form-input" />
                    <button type="submit" class="btn-primary mt-2">Login</button>
                </div>
                <Error />
            </Form>
        </div>
    </Layout>
</template>