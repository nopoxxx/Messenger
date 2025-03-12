import React, { useState } from 'react'
import Input from '../Input/Input'

//@ts-ignore
import classes from './Form.module.css'

interface InputField {
	type: 'email' | 'password' | 'submit' | 'checkbox' | 'text'
	title?: string
	name: string
	disabled?: boolean
}

interface FormProps {
	title: string
	error: string
	inputs: InputField[]
	onSubmit: (values: Record<string, string | boolean>) => void
}

export function Form({ title, error, inputs, onSubmit }: FormProps) {
	const [formValues, setFormValues] = useState<
		Record<string, string | boolean>
	>({})

	const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
		const { name, type, value, checked } = event.target
		setFormValues(prev => ({
			...prev,
			[name]: type === 'checkbox' ? checked : value,
		}))
	}

	const handleSubmit = (event: React.FormEvent) => {
		event.preventDefault()

		const finalValues = inputs.reduce((acc, input) => {
			if (input.type !== 'submit') {
				acc[input.name] =
					input.name in formValues
						? formValues[input.name]
						: input.type === 'checkbox'
						? false
						: ''
			}
			return acc
		}, {} as Record<string, string | boolean>)

		onSubmit(finalValues)
	}

	return (
		<div className={classes.Form}>
			<h1 className={classes.title}>{title}</h1>
			<form className={classes.panel} onSubmit={handleSubmit}>
				<p className={classes.error}>{error ? error : 'ᅠ'}</p>
				{inputs.map(input => (
					<Input
						key={input.name}
						type={input.type}
						title={input.title}
						name={input.name}
						disabled={input.disabled}
						value={
							typeof formValues[input.name] === 'string'
								? (formValues[input.name] as string)
								: ''
						}
						checked={
							typeof formValues[input.name] === 'boolean'
								? (formValues[input.name] as boolean)
								: false
						}
						onChange={handleChange}
					/>
				))}
			</form>
		</div>
	)
}

export default Form
