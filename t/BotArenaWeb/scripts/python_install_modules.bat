rem C:\devel\python\python.exe -m pip install -U pip
rem C:\devel\python\python.exe -m pip install -U matplotlib matplotlib
rem C:\devel\python\python.exe -m pip install -U pandas
C:\devel\anaconda\Scripts\conda.exe install dash
rem C:\devel\anaconda\Scripts\conda.exe update --all --verbose
rem C:\devel\anaconda\Scripts\conda.exe config --add channels conda-forge
rem C:\devel\anaconda\Scripts\conda.exe config --set channel_priority strict
rem C:\devel\anaconda\Scripts\conda.exe install -c intel mkl-service
pause

