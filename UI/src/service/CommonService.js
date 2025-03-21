import { actions, state } from '../../store';

export default class CommonService {
     baseUrl = "http://localhost:8000/api/";
    // baseUrl = "https://prod.smartcollect.co.ug/api/";   //prod environment
    // baseUrl = "https://test.smartcollect.co.ug/api/";   //test environment
//    baseUrl = "../api/";

    // loggedIn = this.checkingAuthentication();

    /**
     * setting the object data to storage
     * @param {*} object
     * @returns
     * @author Bash
     */
    setStorage = object => {
        localStorage.setItem("userData", JSON.stringify(object));
        return object;
    };

    /**
     * for getting the storage data
     * @returns
     * @author Bash
     */
    getStorage = () => {
        let data = localStorage.getItem("userData");
        return JSON.parse(data);
    };



    /**
     * remove the data from  local storage
     * @author Bash
     */
    removeStorage = () => {
        localStorage.removeItem("userData");
    };

    /**
     * for submitting data to the back end
     * @param {*} url
     * @param {*} method
     * @param {*} postData
     * @returns
     * @author Bash
     */
    postToServer = (url, method, postData) => {
        actions.setLoader(true)
        return fetch(url, {
            method: method,
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json"
            },
            body: JSON.stringify(postData)
        })
            .then(res => {
                return res.json();
            })
            .then(json => {
                actions.setLoader(false);
                return json;
            })
            .catch(err => {
                actions.setLoader(false);
                console.log(err);
            });
    };

    /**
     * for submitting data to the back end
     * @param {*} url
     * @param {*} method
     * @param {*} postData
     * @returns
     * @author Bash
     */
    postToServerWithToken = (url, method, postData) => {
        actions.setLoader(true)
        return fetch(url, {
            method: method,
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: "Bearer " + this.getStorage().token
            },
            body: JSON.stringify(postData)
        })
            .then(res => {
                return res.json();
            })
            .then(json => {
                actions.setLoader(false);
                return json;
            })
            .catch(err => {
                actions.setLoader(false);
                console.log(err);
            });
    };

    getFileFromTheServer = (url, method, postData) => {
        actions.setLoader(true)
        return fetch(url, {
            method: method,
            headers: {
            //    "Content-Type": "application/json",
            //    Accept: "application/blo",
                Authorization: "Bearer " + this.getStorage().token
            },
            body: JSON.stringify(postData)
        })
            .then(res => {


                return res.blob();
            })
            .then(blob => {
                actions.setLoader(false);
                return blob;
            })
            .catch(err => {
                actions.setLoader(false);
                console.log(err);
            });
    };


    /**
   * for submitting data to the back end
   * @param {*} url
   * @param {*} method
   * @param {*} postData
   * @returns
   * @author Bash
   */


    postFormDataToServerWithToken = (url, method, formData) => {
        console.log(formData)
        actions.setLoader(true)
        return new Promise((resolve, reject) => {
            fetch(url, {
                method: 'post',
                headers: {
                    // Include the necessary headers for a form submission with a file upload.
                    // Note that the "Authorization" header should be set if needed.
                    // "Content-Type" is set to 'multipart/form-data' automatically when using FormData.
                    Authorization: 'Bearer ' + this.getStorage().token,
                },
                body: formData,
            })
                .then((response) => {
                    // Check if the response status indicates success (e.g., 2xx).
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    // Resolve with the response data.

                    return response.json();
                })
                .then((data) => {
                    actions.setLoader(false);
                    resolve(data);
                })
                .catch((error) => {
                    actions.setLoader(false);
                    reject(error);
                });
        });
    };



    /**
   * getting data from the server
   * @param {*} url
   * @param {*} method
   * @returns
   * @author Bash
   */
    getFromServer = (url, method) => {
        actions.setLoader(true)
        return fetch(url, {
            method: method,
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json"
            }
        })
            .then(res => {
                return res.json();
            })
            .then(json => {
                actions.setLoader(false);
                return json;
            })
            .catch(err => {
                actions.setLoader(false);
                console.log(err);
            });
    };

    /**
     * getting data from the server
     * @param {*} url
     * @param {*} method
     * @returns
     * @author Bash
     */
    getFromServerWithToken = (url, method) => {
        actions.setLoader(true)
        return fetch(url, {
            method: method,
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: "Bearer " + this.getStorage().token
            }
        })
            .then(res => {
                return res.json();
            })
            .then(json => {
                actions.setLoader(false);
                return json;
            })
            .catch(err => {
                actions.setLoader(false);
                console.log(err);
            });
    };

    /**
     * Generic request to the server
     * @param {*} url
     * @param {*} method
     * @param {*} isAuthenticated
     * @param {*} postData
     * @returns
     * @author Bash
     */
    genericRequest = (url, method, isAuthenticated, postData, isFile) => {


        if(isAuthenticated&&isFile){

            return this.getFileFromTheServer(
                this.baseUrl + "" + url,
                method,
                postData
            ).then(file => {
                return file;
            })
        }

        if (method == "GET" || method == "get") {
            if (isAuthenticated) {
                return this.getFromServerWithToken(
                    this.baseUrl + "" + url,
                    method
                ).then(response => {
                    return response;
                });
            } else {
                return this.getFromServer(this.baseUrl + "" + url, method).then(response => {
                    return response;
                });
            }
        } else {


            if (isAuthenticated) {
                return this.postToServerWithToken(
                    this.baseUrl + "" + url,
                    method,
                    postData
                ).then(response => {
                    return response;
                });
            } else {
                return this.postToServer(
                    this.baseUrl + "" + url,
                    method,
                    postData
                ).then(response => {
                    return response;
                });
            }
        }
    };


    uploadFile = async (url, image) => {
        // if (!uploadedFile.value) {
        //     return;
        // }
        const formData = new FormData();
        formData.append('file', image);
        actions.setLoader(true)
        try {
            const response = await fetch(this.baseUrl + "" + url, {
                method: 'POST',
                body: formData,
                headers: {
                    Authorization: `Bearer ${this.getStorage().token}`,
                },
            });
            if (!response.ok) {
                throw new Error('Upload failed');
            }
            actions.setLoader(false);
            const data = await response.json();
            return data
            // console.log(data.message);
        } catch (error) {
            actions.setLoader(false);
            console.error(error.message);
        }
    }



    /**
   * Generic request to the server
   * @param {*} url
   * @param {*} method
   * @param {*} isAuthenticated
   * @param {*} postData
   * @returns
   * @author Bash
   */
    genericFormRequest = (url, method, isAuthenticated, formData) => {
        return this.postFormDataToServerWithToken(
            this.baseUrl + "" + url,
            method,
            formData
        ).then(response => {
            return response;
        });
    };





    /**
     * validation messages
     * @returns
     */
    ValidationMessage = () => {
        let messages = {
            serviceName: [{ required: true, message: "Service name is required" }]
        };
        return messages;
    };

    /**
     * Updating array or pushing new element to the array
     * @param {*} array
     * @param {*} param
     * @param {*} value
     * @param {*} newObject
     * @returns
     */
    updateOrPushObjectByParam = (
        array,
        param,
        value,
        key,
        newObject
    ) => {
        const index = array.findIndex(obj => obj[param] === value);
        if (index !== -1) {
            return [
                ...array.slice(0, index),
                { ...array[index], [key]: newObject[key] },
                ...array.slice(index + 1)
            ];
        }

        return [...array, newObject];
    };

    /**
     * Return error toast message
     * @param {*} toast
     * @param {*} message
     * @returns
     * @author Bash
     */
    showError = (toast, message, duration = 3000) => {
        return toast.add({ severity: 'error', summary: 'Process Failed', detail: message, life: duration });
    };

    /**
     * Return success toast message
     * @param {*} toast
     * @param {*} message
     * @returns
     * @author Bash
     */
    showSuccess = (toast, message, duration = 3000) => {
        return toast.add({ severity: 'success', summary: 'Successful', detail: message, life: duration });
    };


    /**
    * Return info toast message
    * @param {*} toast
    * @param {*} message
    * @returns
    * @author Bash
    */
    showInfo = (toast, message, duration = 3000) => {
        return toast.add({ severity: 'info', summary: 'Information', detail: message, life: duration });
    };

    /**
    * Return info toast message
    * @param {*} toast
    * @param {*} message
    * @returns
    * @author Bash
    */
    showWarning = (toast, message, duration = 3000) => {
        return toast.add({ severity: 'warn', summary: 'Warning', detail: message, life: duration });
    };

    /**
      *
      * @param {*} selectedPermissions
      * @param {*} permission
      * @returns
      * @author Bash
      */
    existingRolePermission = (selectedPermissions, permission) => {
        let result = false;
        selectedPermissions.forEach(element => {
            if (element.permission_id == permission.id) {
                result = true;
            }
        });
        return result;
    };

    /**
     * get current date
     * @returns
     * @author Bash
     */
    getCurrentTimestamp = () => {
        const now = new Date();
        return now;
    };


    /**
     * Generic Redirect
     * @param {*} router
     * @param {*} path
     * @returns
     * @author Bash
     */
    redirect = (router, path) => {
        return router.push(path);
    }


    /**
     * Checking for logged in user
     * @returns
     * @author Bash
     */
    checkingAuthentication = () => {
        let response = false;
        let data = this.getStorage();
        if (data != null && data?.token) {
            response = true
        }
        return response;
    }


    loggedIn = this.checkingAuthentication();

    /**
     * converting file to base 64 string
     * @param file
     * @returns {Promise<unknown>}
     * @author Bashir
     */
    convertExcelToBase64 = (file) => {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => {
                const base64String = reader.result.split(',')[1]; // Extract the base64 part
                resolve(base64String);
            };
            reader.onerror = (error) => reject(error);
            reader.readAsDataURL(file);
        });
    }


    /**
     * formatting money
     * @param {*} number
     * @returns
     * @author Bashir
     */
    commaSeparator = (number) => {
        if (number) {
            return number.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        }else{
            return number
        }
    }


    /**
     * Open or close the model
     * @param {*} value
     * @returns
     * @author Bashir
     */
    toggleModal = (value) => {
        return value
    }


    /**
     * remove letters from a string
     * @param {*} value
     * @returns
     * @author Bashir
     */
    removeLetters = (value) => {
        if (value) {
            return value.replace(/[A-Za-z]/g, '');
        }
    }


    /**
     *
     * @param {*} number
     * @returns
     */
    NumInWords(number) {
        const first = ['', 'one ', 'two ', 'three ', 'four ', 'five ', 'six ', 'seven ', 'eight ', 'nine ', 'ten ', 'eleven ', 'twelve ', 'thirteen ', 'fourteen ', 'fifteen ', 'sixteen ', 'seventeen ', 'eighteen ', 'nineteen '];
        const tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
        const mad = ['', 'thousand', 'million', 'billion', 'trillion'];
        let word = '';

        for (let i = 0; i < mad.length; i++) {
            let tempNumber = number % (100 * Math.pow(1000, i));
            if (Math.floor(tempNumber / Math.pow(1000, i)) !== 0) {
                if (Math.floor(tempNumber / Math.pow(1000, i)) < 20) {
                    word = first[Math.floor(tempNumber / Math.pow(1000, i))] + mad[i] + ' ' + word;
                } else {
                    word = tens[Math.floor(tempNumber / (10 * Math.pow(1000, i)))] + '-' + first[Math.floor(tempNumber / Math.pow(1000, i)) % 10] + mad[i] + ' ' + word;
                }
            }
            tempNumber = number % (Math.pow(1000, i + 1));
            if (Math.floor(tempNumber / (100 * Math.pow(1000, i))) !== 0) word = first[Math.floor(tempNumber / (100 * Math.pow(1000, i)))] + 'hunderd ' + word;
        }
        return word;
    }


    /**
     *
     * @param {*} array
     * @returns
     */
    sumOfAmount = (array) => {
        let totalAmount = 0;
        if (array === undefined || array.length == 0) {
            return totalAmount;
        }
        for (const obj of array) {
            // Use Number() to convert obj.amount to a number, and add it to totalAmount
            totalAmount += Number(obj.amount) || 0;
        }
        return totalAmount;
    }



    /**
     * Validate form field
     * @param {*} value
     * @returns
     */
    validateFormField = (value) => {
        return value === null || value === undefined ||
            (typeof value === 'string' && value.trim().length === 0) ||
            (Array.isArray(value) && value.length === 0) ||
            (typeof value === 'object' && Object.keys(value).length === 0);
    }

    /**
     * check for the required fields if there valid in case they are in valid it returns true else false
     * @param {*} formObj
     * @returns
     */
    validateRequiredFields = (formObj) => {
        let response = false
        Object.entries(formObj).forEach(([key, value]) => {
            if (typeof value === 'boolean' && value) {
                response = true
            }
        });
        return response;
    }


    /**
     * generate graph label
     * @returns
     */
    getMonthsStartingFromCurrent = () => {
        const today = new Date();
        const currentMonthIndex = today.getMonth(); // Get the index of the current month (0-based)
        const monthsLabel = [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ];
        // Use modulo operator to calculate the index for each month
        const rotatedMonths = Array.from({ length: 12 }, (_, i) => monthsLabel[(currentMonthIndex + 12 - i) % 12]);
        return rotatedMonths.reverse();
    }

    /**
     * Organize graph data
     * @param {*} array
     * @param {*} key
     * @returns
     */
    organizeGraphData = (array, key) => {
        let formattedArray = [];
        array.forEach(element => {
            formattedArray.push(element[key]);
        });
        return formattedArray;
    }


    /**
     * checking if permission exists
     * @param {*} permission
     * @returns
     */
    checkPermissions=(permission)=>{
        let permissions=this.getStorage()?.userData?.permissions;
        return permissions.some(element => element.name == permission);
    }

    formatDate=(date)=> {
        const pad = (num) => String(num).padStart(2, '0');
        const day = pad(date.getDate());
        const month = pad(date.getMonth() + 1); // Months are zero-based
        const year = date.getFullYear();
        const hours = pad(date.getHours());
        const minutes = pad(date.getMinutes());
        const seconds = pad(date.getSeconds());
        return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
    }


    generateUUID=()=> {
        // Use the crypto API to generate a UUID
        return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, c =>
            (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
        );
    }

    generateRandomId=() =>{
        const timestamp = Date.now();  // Current timestamp in milliseconds
        const randomNum = Math.floor(Math.random() * 1e9);  // Random number with up to 9 digits
        return Number(`${timestamp}${randomNum}`);
    }

    generateRandomFiveId = () => {
        return Math.floor(10000 + Math.random() * 90000); // Generates a random number between 10000 and 99999
    };

}

