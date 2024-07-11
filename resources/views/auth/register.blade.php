<x-app-layout>

    <main class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0  m-auto  bg-gray-100">
        <section id="content-card" class=" hidden  shadow-xl m-[1%] p-[2%] pt-[1%] rounded-[15px] light-mode w-full md:w-[30%] ">
        </section>
        <section id="content-sign-up" class=" w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-neutral-900 shadow-md overflow-hidden rounded-lg">
            <form method="POST" action="/register">
                @csrf
                <!-- Name -->
                <div>
                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" for="name">
                        Name
                    </label>
                    <input class="border-gray-300 dark:border-gray-700  dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full" id="name" type="text" name="name" required="required" autofocus="autofocus" autocomplete="name">
                </div>

                <!-- Email Address -->
                <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" for="email">
                        Email
                    </label>
                    <input class="border-gray-300 dark:border-gray-700  dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full" id="email" type="email" name="email" required="required" autocomplete="username email">
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" for="password">
                        Password
                    </label>

                    <input class="border-gray-300 dark:border-gray-700  dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full" id="password" type="password" name="password" required="required" autocomplete="new-password" aria-autocomplete="list">

                </div>

                <!-- Confirm Password -->
                <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" for="password_confirmation">
                        Confirm Password
                    </label>

                    <input class="border-gray-300 dark:border-gray-700  dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full" id="password_confirmation" type="password" name="password_confirmation" required="required" autocomplete="new-password">

                </div>

                <div class="flex items-center justify-end mt-4">
                    <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="/login">
                        Already registered?
                    </a>

                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 ms-4">
                        Register
                    </button>
                </div>
            </form>
        </section>
    </main>
</x-app-layout>