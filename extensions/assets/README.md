# Assets

Asset builder for [Symphony][1].

Define asset bundles and basic tasks in a simple configuration file.

Rebuild and store assets on page request if something has changed.

*Uses [cat][2] for bundles, so probably doesn't work on Windows. Tools ([Autoprefixer][3], [Uglify][4], etc.) need to be installed separately.*

*Only use in development environments, disable on production servers.*

## Configuration

Put a `build.php` in your `/workspace/assets/` folder.

It looks like this...

    <?php

    return array(

        // styles

        'styles' => array(

            // bundles

            'bundles' => array(

                // bundle.css

                'bundle.css' => array(

                    '_source1.css',
                    '_source2.css'
                )
            ),

            // tasks

            'tasks' => array(

                // autoprefixer

                '/usr/local/bin/autoprefixer',

                // cssmin

                '/usr/local/bin/cssmin'
            )
        ),

        // scripts

        'scripts' => array(

            // bundles

            'bundles' => array(

                // bundle.js

                'bundle.js' => array(

                    '_source1.js',
                    '_source2.js'
                )
            ),

            'tasks' => array(

                // uglify

                '/usr/local/bin/uglifyjs -mc'
            )
        )
    );

With a simple PHP array, you define different types of assets (like *styles* or *scripts*) as well as multiple bundles and tasks per asset type.

The file structure inside your `/workspace/assets/` folder follows the structure of your configuration file and looks like this...

    /workspace/assets/

        /workspace/assets/styles/

            /workspace/assets/styles/_source1.css
            /workspace/assets/styles/_source2.css

            /workspace/assets/styles/bundle.css

        /workspace/assets/scripts/

            /workspace/assets/scripts/_source1.js
            /workspace/assets/scripts/_source2.js

            /workspace/assets/scripts/bundle.js

Keep in mind, this is just an example. Feel free to name your stuff differently, put your source files into subfolders or use different tools like [TypeScript][5] and [SASS][6].

[1]: http://getsymphony.com
[2]: http://en.wikipedia.org/wiki/Cat_(Unix)
[3]: http://github.com/postcss/autoprefixer
[4]: http://lisperator.net/uglifyjs
[5]: http://typescriptlang.org
[6]: http://sass-lang.com