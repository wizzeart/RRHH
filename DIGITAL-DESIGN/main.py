import os
import sys
# Make sure local modules are found
sys.path.append(os.path.dirname(__file__))

# On Android, we might need to set specific env vars or platform flags
import platform
if platform.system() == 'Linux' and 'ANDROID_ARGUMENT' in os.environ:
    # We are on Android
    os.environ["QT_QPA_PLATFORM"] = "xcb" # Actually PyQt5 on Android usually uses specific platform plugin, but p4a handles it.
    # Note: Kivy uses proper bootstrap. PyQt5 usually needs 'pyqt5' recipe.

import main_app

if __name__ == '__main__':
    main_app.main()
